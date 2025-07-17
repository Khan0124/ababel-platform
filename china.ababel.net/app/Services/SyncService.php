<?php
// app/Services/SyncService.php
namespace App\Services;

use App\Core\Database;
use App\Models\Loading;

class SyncService
{
    private $db;
    private $loadingModel;
    private $portSudanApiUrl;
    private $apiKey;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->loadingModel = new Loading();
        $this->portSudanApiUrl = config('port_sudan_api_url', 'https://ababel.net/app/api/china_sync.php');
        $this->apiKey = config('port_sudan_api_key', 'your-secure-api-key');
    }
    
    /**
     * Sync all pending Port Sudan loadings
     */
    public function syncPendingLoadings()
    {
        $pendingLoadings = $this->loadingModel->getPendingPortSudanSync();
        $results = [];
        
        foreach ($pendingLoadings as $loading) {
            try {
                $result = $this->syncLoadingToPortSudan($loading);
                $results[] = [
                    'loading_id' => $loading['id'],
                    'status' => 'success',
                    'message' => $result['message']
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'loading_id' => $loading['id'],
                    'status' => 'error',
                    'message' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }
    
    /**
     * Sync specific loading to Port Sudan
     */
    public function syncLoadingToPortSudan($loading)
    {
        if (!is_array($loading)) {
            $loading = $this->loadingModel->find($loading);
            if (!$loading) {
                throw new \Exception('Loading not found');
            }
        }
        
        // Prepare sync data
        $syncData = [
            'china_loading_id' => $loading['id'],
            'entry_date' => $loading['shipping_date'],
            'code' => $loading['client_code'],
            'client_name' => $loading['client_name'],
            'loading_number' => $loading['loading_number'],
            'carton_count' => $loading['cartons_count'],
            'container_number' => $loading['container_no'],
            'bill_number' => $loading['claim_number'] ?? '',
            'category' => $loading['item_description'] ?: 'General Cargo',
            'carrier' => 'TBD',
            'expected_arrival' => date('Y-m-d', strtotime($loading['shipping_date'] . ' +30 days')),
            'ship_name' => 'TBD',
            'custom_station' => 'Port Sudan',
            'office' => 'بورتسودان',
            'created_at' => $loading['created_at'] ?? date('Y-m-d H:i:s'),
            'seen_by_port' => 0,
            'synced' => 1
        ];
        
        // Make API call
        $response = $this->makeApiCall('/containers/create', $syncData);
        
        // Log the sync attempt
        $this->logSyncAttempt('create_container', $loading['id'], null, $syncData, $response);
        
        if ($response['success']) {
            // Update loading status to indicate successful sync
            $this->loadingModel->update($loading['id'], ['sync_status' => 'completed']);
            
            return [
                'success' => true,
                'message' => 'Loading synced successfully to Port Sudan',
                'port_sudan_container_id' => $response['container_id'] ?? null
            ];
        } else {
            throw new \Exception('Port Sudan sync failed: ' . ($response['error'] ?? 'Unknown error'));
        }
    }
    
    /**
     * Update loading in Port Sudan
     */
    public function updateLoadingInPortSudan($loadingId, $updateData)
    {
        $loading = $this->loadingModel->find($loadingId);
        if (!$loading || $loading['office'] !== 'port_sudan') {
            return false;
        }
        
        $syncData = array_merge(['china_loading_id' => $loadingId], $updateData);
        
        $response = $this->makeApiCall('/containers/update', $syncData);
        $this->logSyncAttempt('update_container', $loadingId, null, $syncData, $response);
        
        return $response['success'] ?? false;
    }
    
    /**
     * Sync BOL status to Port Sudan
     */
    public function syncBolToPortSudan($loadingId, $bolData)
    {
        $loading = $this->loadingModel->find($loadingId);
        if (!$loading || $loading['office'] !== 'port_sudan') {
            return false;
        }
        
        $syncData = [
            'china_loading_id' => $loadingId,
            'bill_of_lading_status' => $bolData['bill_of_lading_status'],
            'bill_of_lading_date' => $bolData['bill_of_lading_date'] ?? date('Y-m-d')
        ];
        
        if (isset($bolData['bill_of_lading_file'])) {
            $syncData['bill_of_lading_file'] = $bolData['bill_of_lading_file'];
        }
        
        $response = $this->makeApiCall('/containers/update-bol', $syncData);
        $this->logSyncAttempt('update_bol', $loadingId, null, $syncData, $response);
        
        return $response['success'] ?? false;
    }
    
    /**
     * Delete loading from Port Sudan
     */
    public function deleteLoadingFromPortSudan($loadingId)
    {
        $syncData = ['china_loading_id' => $loadingId];
        
        $response = $this->makeApiCall('/containers/delete', $syncData, 'DELETE');
        $this->logSyncAttempt('delete_container', $loadingId, null, $syncData, $response);
        
        return $response['success'] ?? false;
    }
    
    /**
     * Check sync status of a loading
     */
    public function getLoadingSyncStatus($loadingId)
    {
        $stmt = $this->db->query(
            "SELECT * FROM api_sync_log 
             WHERE china_loading_id = ? 
             ORDER BY created_at DESC 
             LIMIT 5", 
            [$loadingId]
        );
        
        return $stmt->fetchAll();
    }
    
    /**
     * Retry failed sync
     */
    public function retryFailedSync($loadingId)
    {
        $loading = $this->loadingModel->find($loadingId);
        if (!$loading) {
            throw new \Exception('Loading not found');
        }
        
        if ($loading['office'] === 'port_sudan') {
            return $this->syncLoadingToPortSudan($loading);
        }
        
        return ['success' => false, 'message' => 'Loading is not assigned to Port Sudan'];
    }
    
    /**
     * Get sync statistics
     */
    public function getSyncStatistics($dateFrom = null, $dateTo = null)
    {
        $whereClause = "WHERE 1=1";
        $params = [];
        
        if ($dateFrom) {
            $whereClause .= " AND DATE(created_at) >= ?";
            $params[] = $dateFrom;
        }
        
        if ($dateTo) {
            $whereClause .= " AND DATE(created_at) <= ?";
            $params[] = $dateTo;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_attempts,
                    COUNT(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 END) as successful,
                    COUNT(CASE WHEN response_code >= 400 THEN 1 END) as failed,
                    COUNT(DISTINCT china_loading_id) as unique_loadings,
                    AVG(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 ELSE 0 END) * 100 as success_rate
                FROM api_sync_log 
                {$whereClause}";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Handle webhook from Port Sudan
     */
    public function handlePortSudanWebhook($data)
    {
        if (!isset($data['action']) || !isset($data['data'])) {
            throw new \Exception('Invalid webhook data');
        }
        
        switch ($data['action']) {
            case 'container_status_updated':
                return $this->handleContainerStatusUpdate($data['data']);
                
            case 'bol_issued':
                return $this->handleBolIssued($data['data']);
                
            case 'container_arrived':
                return $this->handleContainerArrived($data['data']);
                
            default:
                throw new \Exception('Unknown webhook action: ' . $data['action']);
        }
    }
    
    /**
     * Make API call to Port Sudan system
     */
    private function makeApiCall($endpoint, $data, $method = 'POST')
    {
        $url = $this->portSudanApiUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'X-API-Key: ' . $this->apiKey
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new \Exception("CURL Error: " . $error);
        }
        
        $decodedResponse = json_decode($response, true);
        
        if ($httpCode >= 400) {
            $errorMsg = $decodedResponse['error'] ?? "HTTP Error: $httpCode";
            throw new \Exception($errorMsg);
        }
        
        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'http_code' => $httpCode,
            'data' => $decodedResponse
        ] + ($decodedResponse ?: []);
    }
    
    /**
     * Log sync attempt
     */
    private function logSyncAttempt($action, $chinaLoadingId, $containerId, $requestData, $response)
    {
        $this->db->query(
            "INSERT INTO api_sync_log (
                endpoint, method, china_loading_id, container_id, 
                request_data, response_code, response_data, 
                ip_address, created_at
            ) VALUES (?, 'POST', ?, ?, ?, ?, ?, ?, NOW())",
            [
                "/api/china_sync/$action",
                $chinaLoadingId,
                $containerId,
                json_encode($requestData),
                $response['http_code'] ?? 0,
                json_encode($response),
                $_SERVER['REMOTE_ADDR'] ?? 'system'
            ]
        );
    }
    
    /**
     * Handle container status update from Port Sudan
     */
    private function handleContainerStatusUpdate($data)
    {
        if (!isset($data['china_loading_id'])) {
            throw new \Exception('Missing china_loading_id in webhook data');
        }
        
        $loadingId = $data['china_loading_id'];
        $loading = $this->loadingModel->find($loadingId);
        
        if (!$loading) {
            throw new \Exception('Loading not found: ' . $loadingId);
        }
        
        // Update loading status based on Port Sudan data
        $updateData = [];
        
        if (isset($data['status'])) {
            $updateData['status'] = $this->mapPortSudanStatus($data['status']);
        }
        
        if (isset($data['position'])) {
            $updateData['port_sudan_position'] = $data['position'];
        }
        
        if (!empty($updateData)) {
            $this->loadingModel->update($loadingId, $updateData);
        }
        
        return ['success' => true, 'message' => 'Status updated successfully'];
    }
    
    /**
     * Handle BOL issued notification from Port Sudan
     */
    private function handleBolIssued($data)
    {
        if (!isset($data['china_loading_id'])) {
            throw new \Exception('Missing china_loading_id in webhook data');
        }
        
        $loadingId = $data['china_loading_id'];
        
        $updateData = [
            'bill_of_lading_status' => 'issued',
            'bill_of_lading_date' => $data['bol_date'] ?? date('Y-m-d')
        ];
        
        $this->loadingModel->update($loadingId, $updateData);
        
        return ['success' => true, 'message' => 'BOL status updated'];
    }
    
    /**
     * Handle container arrived notification from Port Sudan  
     */
    private function handleContainerArrived($data)
    {
        if (!isset($data['china_loading_id'])) {
            throw new \Exception('Missing china_loading_id in webhook data');
        }
        
        $loadingId = $data['china_loading_id'];
        
        $updateData = [
            'status' => 'arrived',
            'arrival_date' => $data['arrival_date'] ?? date('Y-m-d')
        ];
        
        $this->loadingModel->update($loadingId, $updateData);
        
        return ['success' => true, 'message' => 'Arrival status updated'];
    }
    
    /**
     * Map Port Sudan status to China system status
     */
    private function mapPortSudanStatus($portSudanStatus)
    {
        $statusMap = [
            'At Port' => 'arrived',
            'Customs Cleared' => 'cleared',
            'Delivered' => 'delivered',
            'Empty Returned' => 'completed'
        ];
        
        return $statusMap[$portSudanStatus] ?? 'pending';
    }
}