<?php
// app/Controllers/LoadingController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Loading;
use App\Models\Client;

class LoadingController extends Controller
{
    private $loadingModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->loadingModel = new Loading();
    }
    
    /**
     * Display loadings list
     */
    public function index()
    {
        $this->view('loadings/loading_list', [
            'title' => __('loadings.title')
        ]);
    }
    
    /**
     * Show create form
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('loadings/add_loading', [
                'title' => __('loadings.add_new')
            ]);
            return;
        }
        
        // Handle POST request
        $this->store();
    }
    
    /**
     * Store new loading - FIXED VERSION
     */
    protected function store()
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            // Get client ID from code
            $clientModel = new Client();
            $client = $clientModel->findByCode($_POST['client_code']);
            if (!$client) {
                $_SESSION['error'] = __('messages.invalid_client_code');
                $this->redirect('/loadings/create');
                return;
            }
            
            // Auto-generate claim number
            $claimNumber = $this->generateClaimNumber();
            
            // Check if loading_no is provided
            $loadingNo = trim($_POST['loading_no'] ?? '');
            if (empty($loadingNo)) {
                $_SESSION['error'] = 'Loading number is required';
                $this->redirect('/loadings/create');
                return;
            }
            
            // Prepare data
            $data = [
                'shipping_date' => $_POST['shipping_date'],
                'loading_no' => $loadingNo,
                'claim_number' => $claimNumber,
                'container_no' => strtoupper($_POST['container_no']),
                'client_id' => $client['id'],
                'client_code' => $_POST['client_code'],
                'client_name' => $_POST['client_name'],
                'item_description' => $_POST['item_description'] ?: null,
                'cartons_count' => intval($_POST['cartons_count']),
                'purchase_amount' => floatval($_POST['purchase_amount']),
                'commission_amount' => floatval($_POST['commission_amount']),
                'total_amount' => floatval($_POST['total_amount']),
                'shipping_usd' => floatval($_POST['shipping_usd']),
                'total_with_shipping' => floatval($_POST['total_with_shipping']),
                'office' => $_POST['office'] ?: null,
                'notes' => $_POST['notes'] ?: null,
                'status' => 'pending',
                'created_by' => $_SESSION['user_id']
            ];
            
            // Create loading
            $loadingId = $this->loadingModel->create($data);
            
            // Record financial details in client's account
            $this->recordFinancialDetails($client['id'], $data, $loadingId);
            
            // Handle Port Sudan sync if selected
            if ($data['office'] === 'port_sudan') {
                try {
                    $syncResult = $this->syncToPortSudan($loadingId, $data);
                    if ($syncResult['success']) {
                        $_SESSION['sync_success'] = 'Loading synced to Port Sudan successfully';
                        
                        // Update sync status
                        $this->loadingModel->update($loadingId, [
                            'sync_status' => 'synced',
                            'last_sync_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                } catch (\Exception $e) {
                    error_log("Port Sudan sync failed but loading created: " . $e->getMessage());
                    $_SESSION['sync_warning'] = 'Loading created but sync to Port Sudan failed. It will be retried automatically.';
                    
                    // Update sync status to failed
                    $this->loadingModel->update($loadingId, [
                        'sync_status' => 'failed',
                        'sync_attempts' => 1
                    ]);
                }
            }
            
            // Create office notification for other offices
            if ($data['office'] && $data['office'] !== 'port_sudan') {
                $this->createOfficeNotification($data['office'], $loadingId, $data);
            }
            
            $_SESSION['success'] = 'Loading created successfully';
            $this->redirect('/loadings');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Operation failed: ' . $e->getMessage();
            $this->redirect('/loadings/create');
        }
    }
    
    /**
     * Show edit form
     */
    public function edit($id)
    {
        $loading = $this->loadingModel->find($id);
        
        if (!$loading) {
            $this->redirect('/loadings?error=' . urlencode('Loading not found'));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('loadings/edit_loading', [
                'title' => 'Edit Loading',
                'loading' => $loading
            ]);
            return;
        }
        
        // Handle POST request
        $this->update($id);
    }
    
    /**
     * Update loading - FIXED VERSION
     */
    protected function update($id)
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            $originalLoading = $this->loadingModel->find($id);
            if (!$originalLoading) {
                $_SESSION['error'] = 'Loading not found';
                $this->redirect('/loadings');
                return;
            }
            
            // Get client ID from code
            $clientModel = new Client();
            $client = $clientModel->findByCode($_POST['client_code']);
            if (!$client) {
                $_SESSION['error'] = 'Invalid client code';
                $this->redirect('/loadings/edit/' . $id);
                return;
            }
            
            // Check loading number
            $loadingNo = trim($_POST['loading_no'] ?? '');
            if (empty($loadingNo)) {
                $_SESSION['error'] = 'Loading number is required';
                $this->redirect('/loadings/edit/' . $id);
                return;
            }
            
            // Prepare updated data
            $data = [
                'shipping_date' => $_POST['shipping_date'],
                'loading_no' => $loadingNo,
                'container_no' => strtoupper($_POST['container_no']),
                'client_id' => $client['id'],
                'client_code' => $_POST['client_code'],
                'client_name' => $_POST['client_name'],
                'item_description' => $_POST['item_description'] ?: null,
                'cartons_count' => intval($_POST['cartons_count']),
                'purchase_amount' => floatval($_POST['purchase_amount']),
                'commission_amount' => floatval($_POST['commission_amount']),
                'total_amount' => floatval($_POST['total_amount']),
                'shipping_usd' => floatval($_POST['shipping_usd']),
                'total_with_shipping' => floatval($_POST['total_with_shipping']),
                'office' => $_POST['office'] ?: null,
                'notes' => $_POST['notes'] ?: null,
                'updated_by' => $_SESSION['user_id']
            ];
            
            // Update loading
            $this->loadingModel->update($id, $data);
            
            // Sync changes to Port Sudan if applicable
            if ($originalLoading['office'] === 'port_sudan' || $data['office'] === 'port_sudan') {
                try {
                    $this->syncUpdateToPortSudan($id, $data, $originalLoading);
                    $_SESSION['sync_success'] = 'Changes synced to Port Sudan';
                } catch (\Exception $e) {
                    error_log("Port Sudan update sync failed: " . $e->getMessage());
                    $_SESSION['sync_warning'] = 'Loading updated but sync to Port Sudan failed';
                }
            }
            
            $_SESSION['success'] = 'Loading updated successfully';
            $this->redirect('/loadings');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Operation failed: ' . $e->getMessage();
            $this->redirect('/loadings/edit/' . $id);
        }
    }
    
    /**
     * Delete loading
     */
    public function delete($id)
    {
        try {
            $loading = $this->loadingModel->find($id);
            if (!$loading) {
                $_SESSION['error'] = 'Loading not found';
                $this->redirect('/loadings');
                return;
            }
            
            // Sync deletion to Port Sudan if applicable
            if ($loading['office'] === 'port_sudan') {
                try {
                    $this->syncDeletionToPortSudan($id, $loading);
                } catch (\Exception $e) {
                    error_log("Port Sudan deletion sync failed: " . $e->getMessage());
                }
            }
            
            $this->loadingModel->delete($id);
            $_SESSION['success'] = 'Loading deleted successfully';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Operation failed: ' . $e->getMessage();
        }
        
        $this->redirect('/loadings');
    }
    
    /**
     * Show loading details
     */
    public function show($id)
    {
        $loading = $this->loadingModel->getWithDetails($id);
        
        if (!$loading) {
            $this->redirect('/loadings?error=' . urlencode('Loading not found'));
        }
        
        $this->view('loadings/view_loading', [
            'title' => 'Loading Details',
            'loading' => $loading
        ]);
    }
    
    /**
     * Generate unique claim number
     */
    private function generateClaimNumber()
    {
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "CLM-{$date}-{$random}";
    }
    
    /**
     * Record financial details in client's account
     */
    private function recordFinancialDetails($clientId, $data, $loadingId)
    {
        $db = \App\Core\Database::getInstance();
        
        // Calculate amounts
        $purchase = floatval($data['purchase_amount']);
        $commission = floatval($data['commission_amount']);
        $shippingUSD = floatval($data['shipping_usd']);
        
        // Get exchange rate
        $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'exchange_rate_usd_rmb'");
        $rate = $stmt->fetch();
        $usdToRmb = $rate ? floatval($rate['setting_value']) : 7.20;
        
        $shippingRMB = $shippingUSD * $usdToRmb;
        $totalRMB = $purchase + $commission + $shippingRMB;
        
        // Update client balance
        $db->query(
            "UPDATE clients SET 
                balance_rmb = balance_rmb + ?,
                balance_usd = balance_usd + ?
            WHERE id = ?",
            [$totalRMB, $shippingUSD, $clientId]
        );
        
        // Create transaction record
        $transactionData = [
            'client_id' => $clientId,
            'loading_id' => $loadingId,
            'transaction_type_id' => 1, // GOODS_PURCHASE
            'transaction_date' => date('Y-m-d'),
            'goods_amount_rmb' => $purchase,
            'commission_rmb' => $commission,
            'shipping_usd' => $shippingUSD,
            'total_amount_rmb' => $totalRMB,
            'balance_rmb' => $totalRMB,
            'balance_usd' => $shippingUSD,
            'created_by' => $_SESSION['user_id'],
            'status' => 'approved'
        ];
        
        // Insert transaction
        $columns = implode(',', array_keys($transactionData));
        $placeholders = implode(',', array_fill(0, count($transactionData), '?'));
        $db->query(
            "INSERT INTO transactions ($columns) VALUES ($placeholders)",
            array_values($transactionData)
        );
    }
    
    /**
     * Sync loading data to Port Sudan system - FIXED VERSION
     */
    private function syncToPortSudan($loadingId, $data)
    {
        $syncData = [
            'china_loading_id' => $loadingId,
            'entry_date' => $data['shipping_date'],
            'code' => $data['client_code'],
            'client_name' => $data['client_name'],
            'loading_number' => $data['loading_no'],
            'carton_count' => $data['cartons_count'],
            'container_number' => $data['container_no'],
            'bill_number' => $data['claim_number'] ?? '',
            'category' => $data['item_description'] ?: 'General Cargo',
            'carrier' => 'TBD',
            'expected_arrival' => date('Y-m-d', strtotime($data['shipping_date'] . ' +30 days')),
            'ship_name' => 'TBD',
            'custom_station' => 'Port Sudan',
            'office' => 'بورتسودان'
        ];
        
        // Call Port Sudan API
        $response = $this->callPortSudanAPI('', $syncData, $loadingId);
        
        if ($response['success']) {
            // Create notification for Port Sudan
            $this->createPortSudanNotification($loadingId, $data);
        }
        
        return $response;
    }
    
    /**
     * Sync loading updates to Port Sudan
     */
    private function syncUpdateToPortSudan($loadingId, $newData, $originalData)
    {
        $updateData = [
            'china_loading_id' => $loadingId,
            'entry_date' => $newData['shipping_date'],
            'client_name' => $newData['client_name'],
            'loading_number' => $newData['loading_no'],
            'carton_count' => $newData['cartons_count'],
            'container_number' => $newData['container_no'],
            'category' => $newData['item_description'] ?: 'General Cargo'
        ];
        
        return $this->callPortSudanAPI('/containers/update', $updateData, $loadingId);
    }
    
    /**
     * Sync deletion to Port Sudan
     */
    private function syncDeletionToPortSudan($loadingId, $loadingData)
    {
        $deleteData = ['china_loading_id' => $loadingId];
        return $this->callPortSudanAPI('/containers/delete', $deleteData, $loadingId, 'DELETE');
    }
    
    /**
     * Create notification for Port Sudan office
     */
    private function createPortSudanNotification($loadingId, $data)
    {
        error_log("Port Sudan notification created for loading $loadingId");
    }
    
    /**
     * Call Port Sudan API for synchronization - FIXED VERSION
     */
    private function callPortSudanAPI($endpoint, $data, $loadingId, $method = 'POST')
    {
        $db = \App\Core\Database::getInstance();
        
        // Log the sync attempt
        error_log("Attempting Port Sudan sync for loading $loadingId");
        error_log("Endpoint: $endpoint");
        error_log("Data: " . json_encode($data));
        
        try {
            // Use the china_sync.php endpoint
            $apiUrl = 'https://ababel.net/app/api/china_sync.php' . $endpoint;
            $apiKey = 'AB@1234X-China2Port!';
            
            $postData = json_encode($data);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . $apiKey
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            // Enable verbose output for debugging
            curl_setopt($ch, CURLOPT_VERBOSE, true);
            $verbose = fopen('php://temp', 'w+');
            curl_setopt($ch, CURLOPT_STDERR, $verbose);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            
            // Get verbose output
            rewind($verbose);
            $verboseLog = stream_get_contents($verbose);
            error_log("CURL Verbose: " . $verboseLog);
            
            curl_close($ch);
            
            // Log response
            error_log("HTTP Code: $httpCode");
            error_log("Response: $response");
            error_log("Error: $error");
            
            // Log sync attempt
            if ($this->tableExists('api_sync_log')) {
                $this->logSyncAttempt($endpoint, $loadingId, $data, $httpCode, $response);
            }
            
            if ($error) {
                throw new \Exception("CURL Error: " . $error);
            }
            
            if ($httpCode >= 400) {
                $responseData = json_decode($response, true);
                $errorMsg = isset($responseData['error']) ? $responseData['error'] : "HTTP Error $httpCode";
                throw new \Exception("API Error: " . $errorMsg);
            }
            
            $responseData = json_decode($response, true);
            if (!$responseData) {
                throw new \Exception("Invalid JSON response");
            }
            
            return $responseData;
            
        } catch (\Exception $e) {
            // Log error
            error_log("Port Sudan sync failed: " . $e->getMessage());
            
            // Log failed sync attempt
            if ($this->tableExists('api_sync_log')) {
                $this->logSyncAttempt($endpoint, $loadingId, $data, 0, json_encode(['error' => $e->getMessage()]));
            }
            
            throw $e;
        }
    }
    
    /**
     * Check if table exists in current database
     */
    private function tableExists($tableName)
    {
        $db = \App\Core\Database::getInstance();
        try {
            $stmt = $db->query("SHOW TABLES LIKE ?", [$tableName]);
            return $stmt->fetch() !== false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Log sync attempt
     */
    private function logSyncAttempt($endpoint, $chinaLoadingId, $requestData, $responseCode, $responseData)
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            $sql = "INSERT INTO api_sync_log (endpoint, method, china_loading_id, request_data, response_code, response_data, ip_address, created_at) 
                   VALUES (?, 'POST', ?, ?, ?, ?, ?, NOW())";
            
            $db->query($sql, [
                $endpoint,
                $chinaLoadingId,
                json_encode($requestData),
                $responseCode,
                $responseData,
                $_SERVER['REMOTE_ADDR'] ?? 'system'
            ]);
        } catch (\Exception $e) {
            error_log("Failed to log sync attempt: " . $e->getMessage());
        }
    }
    
    /**
     * Create office notification for non-Port Sudan offices
     */
    private function createOfficeNotification($office, $loadingId, $data)
    {
        $db = \App\Core\Database::getInstance();
        
        $officeNames = [
            'uae' => 'UAE Office',
            'tanzania' => 'Tanzania Office',
            'egypt' => 'Egypt Office'
        ];
        
        $officeName = $officeNames[$office] ?? $office;
        $message = "New loading assigned to {$officeName}: Container {$data['container_no']} for client {$data['client_name']}";
        
        try {
            // Try to insert notification if table exists
            if ($this->tableExists('office_notifications')) {
                $sql = "INSERT INTO office_notifications (office, type, message, reference_type, reference_id, created_at) 
                       VALUES (?, 'loading_assigned', ?, 'loading', ?, NOW())";
                $db->query($sql, [$office, $message, $loadingId]);
            }
        } catch (\Exception $e) {
            error_log("Office notification failed: " . $e->getMessage());
        }
    }
    
    /**
     * Export loadings
     */
    public function export()
    {
        $loadings = $this->loadingModel->getFiltered($_GET);
        
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="loadings_export_' . date('Y_m_d_H_i_s') . '.csv"');
        
        $output = fopen('php://output', 'w');
        
        fputcsv($output, [
            'Shipping Date', 'Loading No', 'Claim Number', 'Container No', 
            'Client Code', 'Client Name', 'Item Description', 'Cartons Count',
            'Purchase Amount', 'Commission Amount', 'Total Amount', 
            'Shipping USD', 'Total with Shipping', 'Office', 'Status', 'Sync Status', 'Notes'
        ]);
        
        foreach ($loadings as $loading) {
            fputcsv($output, [
                $loading['shipping_date'],
                $loading['loading_no'] ?? '',
                $loading['claim_number'] ?? '',
                $loading['container_no'],
                $loading['client_code'],
                $loading['client_name'],
                $loading['item_description'] ?? '',
                $loading['cartons_count'],
                $loading['purchase_amount'],
                $loading['commission_amount'],
                $loading['total_amount'],
                $loading['shipping_usd'],
                $loading['total_with_shipping'],
                $loading['office'] ?? '',
                $loading['status'],
                $loading['sync_status'] ?? 'N/A',
                $loading['notes'] ?? ''
            ]);
        }
        
        fclose($output);
        exit;
    }
    
    /**
     * Update loading status
     */
    public function updateStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/loadings');
            return;
        }
        
        $status = $_POST['status'] ?? '';
        $validStatuses = ['pending', 'shipped', 'arrived', 'cleared', 'cancelled'];
        
        if (!in_array($status, $validStatuses)) {
            $_SESSION['error'] = 'Invalid status';
            $this->redirect('/loadings');
            return;
        }
        
        try {
            $this->loadingModel->update($id, [
                'status' => $status,
                'updated_by' => $_SESSION['user_id']
            ]);
            
            $_SESSION['success'] = 'Status updated successfully';
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Operation failed';
        }
        
        $this->redirect('/loadings');
    }
    
    /**
     * Manually retry sync for a loading
     */
    public function retrySync($id)
    {
        try {
            $loading = $this->loadingModel->find($id);
            if (!$loading) {
                $_SESSION['error'] = 'Loading not found';
                $this->redirect('/loadings');
                return;
            }
            
            if ($loading['office'] !== 'port_sudan') {
                $_SESSION['error'] = 'This loading is not assigned to Port Sudan';
                $this->redirect('/loadings');
                return;
            }
            
            // Prepare sync data
            $syncData = [
                'shipping_date' => $loading['shipping_date'],
                'loading_no' => $loading['loading_no'],
                'claim_number' => $loading['claim_number'],
                'container_no' => $loading['container_no'],
                'client_code' => $loading['client_code'],
                'client_name' => $loading['client_name'],
                'item_description' => $loading['item_description'],
                'cartons_count' => $loading['cartons_count'],
                'office' => 'port_sudan'
            ];
            
            $syncResult = $this->syncToPortSudan($id, $syncData);
            
            if ($syncResult['success']) {
                $_SESSION['success'] = 'Loading synced to Port Sudan successfully';
                
                // Update sync status
                $this->loadingModel->update($id, [
                    'sync_status' => 'synced',
                    'last_sync_at' => date('Y-m-d H:i:s')
                ]);
            } else {
                $_SESSION['error'] = 'Sync failed: ' . ($syncResult['message'] ?? 'Unknown error');
            }
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Sync failed: ' . $e->getMessage();
        }
        
        $this->redirect('/loadings');
    }
}