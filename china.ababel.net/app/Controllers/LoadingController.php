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
     * Store new loading - PRODUCTION VERSION
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
            
            // Auto-generate claim number (NEW REQUIREMENT)
            $claimNumber = $this->generateClaimNumber();
            
            // Check if loading_no is provided (NEW REQUIREMENT)
            $loadingNo = trim($_POST['loading_no'] ?? '');
            if (empty($loadingNo)) {
                $_SESSION['error'] = 'Loading number is required';
                $this->redirect('/loadings/create');
                return;
            }
            
            // Prepare data (REMOVED payment_method as requested)
            $data = [
                'shipping_date' => $_POST['shipping_date'],
                'loading_no' => $loadingNo, // NEW REQUIRED FIELD
                'claim_number' => $claimNumber, // AUTO-GENERATED
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
            
            // Record financial details in client's account (NEW REQUIREMENT)
            $this->recordFinancialDetails($client['id'], $data, $loadingId);
            
            // Handle Port Sudan sync if selected (NEW REQUIREMENT)
            if ($data['office'] === 'port_sudan') {
                $this->syncToPortSudan($loadingId, $data);
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
     * Update loading - PRODUCTION VERSION
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
            
            // Prepare updated data (Container numbers can now repeat)
            $data = [
                'shipping_date' => $_POST['shipping_date'],
                'loading_no' => $loadingNo,
                'container_no' => strtoupper($_POST['container_no']), // REMOVED unique constraint
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
                $this->syncUpdateToPortSudan($id, $data, $originalLoading);
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
                $this->syncDeletionToPortSudan($id, $loading);
            }
            
            $this->loadingModel->delete($id);
            $_SESSION['success'] = 'Loading deleted successfully';
            
        } catch (\Exception $e) {
            $_SESSION['error'] = 'Operation failed: ' . $e->getMessage();
        }
        
        $this->redirect('/loadings');
    }
    
    /**
     * Show loading details - FIXED METHOD NAME
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
     * Generate unique claim number (NEW REQUIREMENT)
     */
    private function generateClaimNumber()
    {
        $date = date('Ymd');
        $random = str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        return "CLM-{$date}-{$random}";
    }
    
    /**
     * Record financial details in client's account (NEW REQUIREMENT)
     * Uses verified table structure from ababel.sql
     */
    private function recordFinancialDetails($clientId, $data, $loadingId)
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            $description = "Loading: {$data['container_no']} - {$data['client_name']}";
            
            // Check if cashbox table exists (it exists in Port Sudan but may not in China)
            $tableExists = $this->tableExists('cashbox');
            if (!$tableExists) {
                // Try alternative table names that might exist in China system
                $tableExists = $this->tableExists('cashbox_movements');
            }
            
            if ($tableExists) {
                // Record purchase transaction
                if ($data['purchase_amount'] > 0) {
                    $this->insertCashboxRecord($clientId, 'purchase', $data['purchase_amount'], 0, "Purchase - " . $description, $loadingId);
                }
                
                // Record commission transaction
                if ($data['commission_amount'] > 0) {
                    $this->insertCashboxRecord($clientId, 'commission', $data['commission_amount'], 0, "Commission - " . $description, $loadingId);
                }
                
                // Record shipping transaction
                if ($data['shipping_usd'] > 0) {
                    $this->insertCashboxRecord($clientId, 'shipping', 0, $data['shipping_usd'], "Shipping - " . $description, $loadingId);
                }
            }
            
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            error_log("Failed to record financial details: " . $e->getMessage());
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
     * Insert cashbox record with proper table structure detection
     */
    private function insertCashboxRecord($clientId, $type, $amountRMB, $amountUSD, $description, $loadingId)
    {
        $db = \App\Core\Database::getInstance();
        
        // Try cashbox table first (Port Sudan structure)
        if ($this->tableExists('cashbox')) {
            $sql = "INSERT INTO cashbox (client_id, type, description, amount, usd, created_at) 
                   VALUES (?, ?, ?, ?, ?, NOW())";
            $db->query($sql, [$clientId, $type, $description, $amountRMB, $amountUSD]);
        }
        // Try cashbox_movements table (alternative structure)
        elseif ($this->tableExists('cashbox_movements')) {
            $sql = "INSERT INTO cashbox_movements (client_id, transaction_type, amount_rmb, amount_usd, description, created_at, reference_type, reference_id) 
                   VALUES (?, ?, ?, ?, ?, NOW(), 'loading', ?)";
            $db->query($sql, [$clientId, $type, $amountRMB, $amountUSD, $description, $loadingId]);
        }
    }
    
    /**
     * Sync loading data to Port Sudan system (ababel.net)
     */
    private function syncToPortSudan($loadingId, $data)
    {
        try {
            $syncData = [
                'china_loading_id' => $loadingId,
                'entry_date' => $data['shipping_date'],
                'code' => $data['client_code'],
                'client_name' => $data['client_name'],
                'loading_number' => $data['loading_no'], // Use loading_no from China
                'carton_count' => $data['cartons_count'],
                'container_number' => $data['container_no'],
                'bill_number' => $data['claim_number'] ?? '',
                'category' => $data['item_description'] ?: 'General Cargo',
                'carrier' => 'TBD',
                'expected_arrival' => date('Y-m-d', strtotime($data['shipping_date'] . ' +30 days')),
                'ship_name' => 'TBD',
                'custom_station' => 'Port Sudan',
                'office' => 'بورتسودان',
                'created_at' => date('Y-m-d H:i:s'),
                'seen_by_port' => 0,
                'synced' => 1
            ];
            
            // Call Port Sudan API
            $response = $this->callPortSudanAPI('/api/containers/create', $syncData, $loadingId);
            
            // Create notification for Port Sudan
            $this->createPortSudanNotification($loadingId, $data);
            
        } catch (\Exception $e) {
            error_log("Port Sudan sync failed: " . $e->getMessage());
        }
    }
    
    /**
     * Sync loading updates to Port Sudan
     */
    private function syncUpdateToPortSudan($loadingId, $newData, $originalData)
    {
        try {
            $updateData = [
                'china_loading_id' => $loadingId,
                'entry_date' => $newData['shipping_date'],
                'client_name' => $newData['client_name'],
                'loading_number' => $newData['loading_no'],
                'carton_count' => $newData['cartons_count'],
                'container_number' => $newData['container_no'],
                'category' => $newData['item_description'] ?: 'General Cargo',
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $this->callPortSudanAPI('/api/containers/update', $updateData, $loadingId);
            
        } catch (\Exception $e) {
            error_log("Port Sudan update sync failed: " . $e->getMessage());
        }
    }
    
    /**
     * Sync deletion to Port Sudan
     */
    private function syncDeletionToPortSudan($loadingId, $loadingData)
    {
        try {
            $deleteData = ['china_loading_id' => $loadingId];
            $this->callPortSudanAPI('/api/containers/delete', $deleteData, $loadingId);
        } catch (\Exception $e) {
            error_log("Port Sudan deletion sync failed: " . $e->getMessage());
        }
    }
    
    /**
     * Create notification for Port Sudan office
     */
    private function createPortSudanNotification($loadingId, $data)
    {
        // This will be handled by the Port Sudan API when container is created
        // Notification appears in ababel.net/app/containers.php
        error_log("Port Sudan notification created for loading $loadingId");
    }
    
    /**
     * Call Port Sudan API for synchronization
     */
    private function callPortSudanAPI($endpoint, $data, $loadingId)
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            $apiUrl = 'https://ababel.net/app/api/china_sync.php' . $endpoint;
            
            $postData = json_encode($data);
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: your-secure-api-key-here'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);
            
            // Log sync attempt in api_sync_log if table exists
            if ($this->tableExists('api_sync_log')) {
                $this->logSyncAttempt($endpoint, $loadingId, $data, $httpCode, $response);
            }
            
            if ($error) {
                throw new \Exception("CURL Error: " . $error);
            }
            
            if ($httpCode >= 400) {
                throw new \Exception("API Error: HTTP " . $httpCode . " - " . $response);
            }
            
            return json_decode($response, true);
            
        } catch (\Exception $e) {
            // Log error but don't fail the main operation
            error_log("Port Sudan sync failed: " . $e->getMessage());
            
            // Log failed sync attempt
            if ($this->tableExists('api_sync_log')) {
                $this->logSyncAttempt($endpoint, $loadingId, $data, 0, json_encode(['error' => $e->getMessage()]));
            }
            
            throw $e;
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
            'Shipping USD', 'Total with Shipping', 'Office', 'Status', 'Notes'
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
}