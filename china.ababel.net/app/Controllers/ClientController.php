<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Client;

class ClientController extends Controller
{
    private $clientModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->clientModel = new Client();
    }
    
    public function index()
    {
    $clientModel = new Client();
    
    // Use the new method that includes transaction count
    $clients = $clientModel->allWithTransactionCount(['status' => 'active']);
    
    $this->view('clients/index', [
        'title' => __('clients.title'),
        'clients' => $clients
    ]);
}
    
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'client_code' => $_POST['client_code'],
                'name' => $_POST['name'],
                'name_ar' => $_POST['name_ar'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'address' => $_POST['address'],
                'credit_limit' => $_POST['credit_limit'] ?? 0
            ];
            
            try {
                $clientId = $this->clientModel->create($data);
                $this->redirect('/clients?success=Client created successfully');
            } catch (\Exception $e) {
                $this->view('clients/create', [
                    'title' => 'Add Client',
                    'error' => 'Error creating client: ' . $e->getMessage(),
                    'data' => $data
                ]);
                return;
            }
        }
        
        $this->view('clients/create', [
            'title' => 'Add Client'
        ]);
    }
    
    public function edit($id)
    {
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->redirect('/clients?error=Client not found');
        }
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'],
                'name_ar' => $_POST['name_ar'],
                'phone' => $_POST['phone'],
                'email' => $_POST['email'],
                'address' => $_POST['address'],
                'credit_limit' => $_POST['credit_limit'] ?? 0,
                'status' => $_POST['status']
            ];
            
            try {
                $this->clientModel->update($id, $data);
                $this->redirect('/clients?success=Client updated successfully');
            } catch (\Exception $e) {
                $this->view('clients/edit', [
                    'title' => 'Edit Client',
                    'error' => 'Error updating client: ' . $e->getMessage(),
                    'client' => array_merge($client, $data)
                ]);
                return;
            }
        }
        
        $this->view('clients/edit', [
            'title' => 'Edit Client',
            'client' => $client
        ]);
    }
    
    public function statement($id)
    {
        $client = $this->clientModel->find($id);
        
        if (!$client) {
            $this->redirect('/clients?error=Client not found');
        }
        
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        
        $transactions = $this->clientModel->getStatement($id, $startDate, $endDate);
        
        $this->view('reports/client-statement', [
            'title' => 'Client Statement - ' . $client['name'],
            'client' => $client,
            'transactions' => $transactions,
            'startDate' => $startDate,
            'endDate' => $endDate
        ]);
    }
}