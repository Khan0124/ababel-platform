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
     * Store new loading
     */
    protected function store()
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            // Validate container number uniqueness
            $stmt = $db->query("SELECT id FROM loadings WHERE container_no = ?", [$_POST['container_no']]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = __('loadings.duplicate_container');
                $this->redirect('/loadings/create');
                return;
            }
            
            // Get client ID from code
            $clientModel = new Client();
            $client = $clientModel->findByCode($_POST['client_code']);
            if (!$client) {
                $_SESSION['error'] = __('messages.invalid_client_code');
                $this->redirect('/loadings/create');
                return;
            }
            
            // Prepare data
            $data = [
                'shipping_date' => $_POST['shipping_date'],
                'payment_method' => $_POST['payment_method'],
                'claim_number' => $_POST['claim_number'] ?: null,
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
            
            // Create office notification if office is selected
            if ($data['office']) {
                $this->createOfficeNotification($data['office'], $loadingId, $data);
            }
            
            $_SESSION['success'] = __('loadings.loading_created');
            $this->redirect('/loadings');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = __('messages.operation_failed') . ': ' . $e->getMessage();
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
            $this->redirect('/loadings?error=' . urlencode(__('messages.not_found')));
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view('loadings/edit_loading', [
                'title' => __('loadings.edit_loading'),
                'loading' => $loading
            ]);
            return;
        }
        
        // Handle POST request
        $this->update($id);
    }
    
    /**
     * Update loading
     */
    protected function update($id)
    {
        $db = \App\Core\Database::getInstance();
        
        try {
            // Validate container number uniqueness (excluding current record)
            $stmt = $db->query("SELECT id FROM loadings WHERE container_no = ? AND id != ?", 
                              [$_POST['container_no'], $id]);
            if ($stmt->fetch()) {
                $_SESSION['error'] = __('loadings.duplicate_container');
                $this->redirect('/loadings/edit/' . $id);
                return;
            }
            
            // Get client ID from code
            $clientModel = new Client();
            $client = $clientModel->findByCode($_POST['client_code']);
            if (!$client) {
                $_SESSION['error'] = __('messages.invalid_client_code');
                $this->redirect('/loadings/edit/' . $id);
                return;
            }
            
            // Prepare data
            $data = [
                'shipping_date' => $_POST['shipping_date'],
                'payment_method' => $_POST['payment_method'],
                'claim_number' => $_POST['claim_number'] ?: null,
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
            
            $_SESSION['success'] = __('loadings.loading_updated');
            $this->redirect('/loadings');
            
        } catch (\Exception $e) {
            $_SESSION['error'] = __('messages.operation_failed') . ': ' . $e->getMessage();
            $this->redirect('/loadings/edit/' . $id);
        }
    }
    
    /**
     * View loading details
     */
    public function show($id)
    {
        $loading = $this->loadingModel->getWithDetails($id);
        
        if (!$loading) {
            $this->redirect('/loadings?error=' . urlencode(__('messages.not_found')));
        }
        
        $this->view('loadings/view_loading', [
            'title' => __('loadings.view_details'),
            'loading' => $loading
        ]);
    }
    
    /**
     * Update loading status (AJAX)
     */
    public function updateStatus($id)
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Invalid request method']);
            return;
        }
        
        $data = json_decode(file_get_contents('php://input'), true);
        $status = $data['status'] ?? '';
        
        $validStatuses = ['pending', 'shipped', 'arrived', 'cleared', 'cancelled'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            return;
        }
        
        try {
            $this->loadingModel->update($id, [
                'status' => $status,
                'updated_by' => $_SESSION['user_id']
            ]);
            
            // If marking as arrived, create notification
            if ($status === 'arrived') {
                $loading = $this->loadingModel->find($id);
                if ($loading && $loading['office']) {
                    $this->createOfficeNotification($loading['office'], $id, $loading, 'arrived');
                }
            }
            
            echo json_encode(['success' => true, 'message' => __('messages.updated_successfully')]);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    /**
     * Delete loading
     */
    public function delete($id)
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('/loadings?error=' . urlencode(__('messages.access_denied')));
        }
        
        try {
            $this->loadingModel->delete($id);
            $_SESSION['success'] = __('loadings.loading_deleted');
        } catch (\Exception $e) {
            $_SESSION['error'] = __('messages.operation_failed');
        }
        
        $this->redirect('/loadings');
    }
    
    /**
     * Create office notification
     */
    protected function createOfficeNotification($office, $loadingId, $loadingData, $type = 'new')
    {
        $db = \App\Core\Database::getInstance();
        
        $message = $type === 'new' 
            ? sprintf('New container %s assigned to your office', $loadingData['container_no'])
            : sprintf('Container %s has arrived', $loadingData['container_no']);
        
        $notificationData = [
            'office' => $office,
            'type' => $type === 'new' ? 'new_container' : 'container_arrived',
            'reference_id' => $loadingId,
            'reference_type' => 'loading',
            'message' => $message
        ];
        
        $sql = "INSERT INTO office_notifications (office, type, reference_id, reference_type, message) 
                VALUES (?, ?, ?, ?, ?)";
        
        $db->query($sql, [
            $notificationData['office'],
            $notificationData['type'],
            $notificationData['reference_id'],
            $notificationData['reference_type'],
            $notificationData['message']
        ]);
    }
    
    /**
     * Export to Excel
     */
    public function export()
    {
        // Get filtered data similar to index method
        $filters = [
            'date_from' => $_GET['date_from'] ?? '',
            'date_to' => $_GET['date_to'] ?? '',
            'client_code' => $_GET['client_code'] ?? '',
            'container_no' => $_GET['container_no'] ?? '',
            'office' => $_GET['office'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $loadings = $this->loadingModel->getFiltered($filters);
        
        // Generate Excel file
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="loadings_' . date('Y-m-d') . '.xls"');
        header('Cache-Control: max-age=0');
        
        // Output Excel content
        $this->view('loadings/export_excel', [
            'loadings' => $loadings
        ]);
    }
}