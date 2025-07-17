<?php
namespace App\Models;

use App\Core\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    
    public function getWithDetails($id)
    {
        $sql = "
            SELECT t.*, 
                   c.name as client_name, 
                   c.client_code,
                   tt.name as transaction_type_name,
                   u.full_name as created_by_name
            FROM transactions t
            LEFT JOIN clients c ON t.client_id = c.id
            JOIN transaction_types tt ON t.transaction_type_id = tt.id
            LEFT JOIN users u ON t.created_by = u.id
            WHERE t.id = ?
        ";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    public function getLatest($limit = 10)
    {
        $sql = "
            SELECT t.*, 
                   c.name as client_name,
                   tt.name as transaction_type_name
            FROM transactions t
            LEFT JOIN clients c ON t.client_id = c.id
            JOIN transaction_types tt ON t.transaction_type_id = tt.id
            ORDER BY t.created_at DESC
            LIMIT ?
        ";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    public function generateTransactionNo()
    {
        $year = date('Y');
        $sql = "SELECT MAX(CAST(SUBSTRING(transaction_no, 6) AS UNSIGNED)) as max_no 
                FROM transactions 
                WHERE transaction_no LIKE ?";
        
        $stmt = $this->db->query($sql, ["TRX-$year-%"]);
        $result = $stmt->fetch();
        
        $nextNo = ($result['max_no'] ?? 0) + 1;
        return sprintf("TRX-%s-%06d", $year, $nextNo);
    }
    
    public function createWithCashbox($transactionData, $cashboxData = null)
    {
        $conn = $this->db->getConnection();
        
        try {
            $conn->beginTransaction();
            
            // Create transaction
            $transactionId = $this->create($transactionData);
            
            // Create cashbox movement if needed
            if ($cashboxData && !empty($cashboxData)) {
                $cashbox = new Cashbox();
                $cashboxData['transaction_id'] = $transactionId;
                $cashbox->create($cashboxData);
            }
            
            // Update client balance
            if (isset($transactionData['client_id'])) {
                $this->updateClientBalance($transactionData['client_id']);
            }
            
            $conn->commit();
            return $transactionId;
            
        } catch (\Exception $e) {
            $conn->rollBack();
            throw $e;
        }
    }
    
    private function updateClientBalance($clientId)
    {
        $sql = "
            UPDATE clients c
            SET 
                balance_rmb = (
                    SELECT COALESCE(SUM(balance_rmb), 0) 
                    FROM transactions 
                    WHERE client_id = c.id AND status = 'approved'
                ),
                balance_usd = (
                    SELECT COALESCE(SUM(balance_usd), 0) 
                    FROM transactions 
                    WHERE client_id = c.id AND status = 'approved'
                )
            WHERE c.id = ?
        ";
        
        $this->db->query($sql, [$clientId]);
    }
}