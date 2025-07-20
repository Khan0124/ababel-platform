<?php
namespace App\Controllers;

use App\Core\Controller;
use App\Models\Transaction;
use App\Models\Client;
use App\Models\Cashbox;

class TransactionController extends Controller
{
    private $transactionModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->transactionModel = new Transaction();
    }
    
    public function index()
    {
        $conditions = [];
        
        if (isset($_GET['client_id'])) {
            $conditions['client_id'] = $_GET['client_id'];
        }
        
        if (isset($_GET['status'])) {
            $conditions['status'] = $_GET['status'];
        }
        
        $transactions = $this->transactionModel->all($conditions, 'transaction_date DESC', 50);
        
        $this->view('transactions/index', [
            'title' => __('transactions.title'),
            'transactions' => $transactions
        ]);
    }
    
    public function create()
    {
        $clientModel = new Client();
        $clients = $clientModel->all(['status' => 'active'], 'name');
        
        // Get banks list from settings
        $db = \App\Core\Database::getInstance();
        $stmt = $db->query("SELECT setting_value FROM settings WHERE setting_key = 'banks_list'");
        $banksResult = $stmt->fetch();
        $banksList = $banksResult ? explode(',', $banksResult['setting_value']) : [];
        $banksList = array_map('trim', $banksList);
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Check for duplicate transaction number in the same bank
            if (!empty($_POST['transaction_no']) && !empty($_POST['bank_name'])) {
                $checkSql = "SELECT COUNT(*) as count FROM transactions 
                            WHERE transaction_no = ? AND bank_name = ?";
                $stmt = $db->query($checkSql, [$_POST['transaction_no'], $_POST['bank_name']]);
                $result = $stmt->fetch();
                
                if ($result['count'] > 0) {
                    $this->view('transactions/create', [
                        'title' => __('transactions.add_new'),
                        'error' => __('validation.transaction_duplicate_bank'),
                        'clients' => $clients,
                        'banksList' => $banksList,
                        'data' => $_POST
                    ]);
                    return;
                }
            }
            
            $transactionData = [
                'transaction_no' => $_POST['transaction_no'] ?? $this->transactionModel->generateTransactionNo(),
                'client_id' => $_POST['client_id'],
                'transaction_type_id' => $_POST['transaction_type_id'],
                'transaction_date' => $_POST['transaction_date'],
                'description' => $_POST['description'],
                'invoice_no' => $_POST['invoice_no'] ?? null,
                'loading_no' => $_POST['loading_no'] ?? null,
                'bank_name' => $_POST['bank_name'] ?? null,
                'goods_amount_rmb' => $_POST['goods_amount_rmb'] ?? 0,
                'commission_rmb' => $_POST['commission_rmb'] ?? 0,
                'total_amount_rmb' => $_POST['total_amount_rmb'] ?? 0,
                'payment_rmb' => $_POST['payment_rmb'] ?? 0,
                'balance_rmb' => $_POST['balance_rmb'] ?? 0,
                'shipping_usd' => $_POST['shipping_usd'] ?? 0,
                'payment_usd' => $_POST['payment_usd'] ?? 0,
                'balance_usd' => $_POST['balance_usd'] ?? 0,
                'payment_sdg' => $_POST['payment_sdg'] ?? 0,
                'rate_usd_rmb' => $_POST['rate_usd_rmb'] ?? null,
                'rate_sdg_rmb' => $_POST['rate_sdg_rmb'] ?? null,
                'created_by' => $_SESSION['user_id'],
                'status' => 'pending'
            ];
            
            // Prepare cashbox data if affects cashbox
            $cashboxData = null;
            if (isset($_POST['affects_cashbox']) && $_POST['affects_cashbox']) {
                $cashboxData = [
                    'movement_date' => $_POST['transaction_date'],
                    'movement_type' => $_POST['cashbox_type'], // in/out
                    'category' => $_POST['cashbox_category'],
                    'amount_rmb' => $_POST['cashbox_rmb'] ?? 0,
                    'amount_usd' => $_POST['cashbox_usd'] ?? 0,
                    'amount_sdg' => $_POST['cashbox_sdg'] ?? 0,
                    'bank_name' => $_POST['bank_name'] ?? null,
                    'tt_number' => $_POST['tt_number'] ?? null,
                    'receipt_no' => $_POST['receipt_no'] ?? null,
                    'description' => $_POST['cashbox_description'] ?? null,
                    'created_by' => $_SESSION['user_id']
                ];
            }
            
            try {
                $transactionId = $this->transactionModel->createWithCashbox($transactionData, $cashboxData);
                $this->redirect('/transactions?success=' . urlencode(__('transactions.transaction_created')));
            } catch (\Exception $e) {
                $this->view('transactions/create', [
                    'title' => __('transactions.add_new'),
                    'error' => __('messages.operation_failed') . ': ' . $e->getMessage(),
                    'clients' => $clients,
                    'banksList' => $banksList,
                    'data' => $transactionData
                ]);
                return;
            }
        }
        
        $this->view('transactions/create', [
            'title' => __('transactions.add_new'),
            'clients' => $clients,
            'banksList' => $banksList
        ]);
    }
    
    // Changed from 'view' to 'show' to avoid conflict with parent class
    public function show($id)
    {
        $transaction = $this->transactionModel->getWithDetails($id);
        
        if (!$transaction) {
            $this->redirect('/transactions?error=' . urlencode(__('messages.transaction_not_found')));
        }
        
        $this->view('transactions/view', [
            'title' => __('transactions.details'),
            'transaction' => $transaction
        ]);
    }
    
    /**
     * Show approval confirmation page (GET request)
     */
    public function showApprove($id)
    {
        // Check authorization
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'accountant') {
            $this->redirect('/transactions?error=' . urlencode(__('messages.unauthorized')));
        }
        
        $transaction = $this->transactionModel->getWithDetails($id);
        
        if (!$transaction) {
            $this->redirect('/transactions?error=' . urlencode(__('messages.transaction_not_found')));
        }
        
        // If transaction is already approved
        if ($transaction['status'] === 'approved') {
            $this->redirect('/transactions/view/' . $id . '?error=' . urlencode(__('messages.transaction_already_approved')));
        }
        
        $this->view('transactions/approve', [
            'title' => __('transactions.approve'),
            'transaction' => $transaction
        ]);
    }
    
    /**
     * Process transaction approval (POST request)
     */
    public function approve($id)
    {
        if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'accountant') {
            $this->redirect('/transactions?error=' . urlencode(__('messages.unauthorized')));
        }
        
        $db = \App\Core\Database::getInstance();
        
        try {
            $db->beginTransaction();
            
            // Get transaction details
            $transaction = $this->transactionModel->find($id);
            if (!$transaction) {
                throw new \Exception(__('messages.transaction_not_found'));
            }
            
            // Update transaction status
            $this->transactionModel->update($id, [
                'status' => 'approved',
                'approved_by' => $_SESSION['user_id'],
                'approved_at' => date('Y-m-d H:i:s')
            ]);
            
            // If this is a payment transaction, update client balance
            if ($transaction['payment_rmb'] > 0 || $transaction['payment_usd'] > 0 || $transaction['payment_sdg'] > 0) {
                $clientModel = new Client();
                $client = $clientModel->find($transaction['client_id']);
                
                if ($client) {
                   $newBalanceRmb = $client['balance_rmb'] - $transaction['payment_rmb'];
                   $newBalanceUsd = $client['balance_usd'] - $transaction['payment_usd'];
                    
                    $clientModel->update($transaction['client_id'], [
                        'balance_rmb' => $newBalanceRmb,
                        'balance_usd' => $newBalanceUsd
                    ]);
                    
                    // Create cashbox movement if payment was made
                    if ($transaction['payment_rmb'] > 0 || $transaction['payment_usd'] > 0 || $transaction['payment_sdg'] > 0) {
                        $cashboxModel = new Cashbox();
                        $cashboxModel->create([
                            'transaction_id' => $id,
                            'movement_date' => date('Y-m-d'),
                            'movement_type' => 'in',
                            'category' => 'payment_received',
                            'amount_rmb' => $transaction['payment_rmb'],
                            'amount_usd' => $transaction['payment_usd'],
                            'amount_sdg' => $transaction['payment_sdg'],
                            'bank_name' => $transaction['bank_name'],
                            'description' => __('transactions.payment_from_client') . ': ' . $client['name'],
                            'created_by' => $_SESSION['user_id']
                        ]);
                    }
                }
            }
            
            $db->commit();
            $this->redirect('/transactions/view/' . $id . '?success=' . urlencode(__('transactions.transaction_approved')));
            
        } catch (\Exception $e) {
            $db->rollback();
            $this->redirect('/transactions/view/' . $id . '?error=' . urlencode(__('messages.error_approving_transaction') . ': ' . $e->getMessage()));
        }
    }
    
    /**
     * Delete transaction (soft delete)
     */
    public function delete($id)
    {
        if ($_SESSION['user_role'] !== 'admin') {
            $this->redirect('/transactions?error=' . urlencode(__('messages.unauthorized')));
        }
        
        try {
            $this->transactionModel->update($id, [
                'status' => 'cancelled',
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $_SESSION['user_id']
            ]);
            
            $this->redirect('/transactions?success=' . urlencode(__('transactions.transaction_cancelled')));
        } catch (\Exception $e) {
            $this->redirect('/transactions?error=' . urlencode(__('messages.error_deleting_transaction')));
        }
    }
}