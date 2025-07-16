<?php
namespace App\Models;

use App\Core\Model;

class Client extends Model
{
    protected $table = 'clients';
    
    public function getWithBalance()
    {
        $sql = "
            SELECT c.*, 
                   COALESCE(SUM(t.balance_rmb), 0) as current_balance_rmb,
                   COALESCE(SUM(t.balance_usd), 0) as current_balance_usd,
                   COUNT(t.id) as transaction_count
            FROM clients c
            LEFT JOIN transactions t ON c.id = t.client_id AND t.status = 'approved'
            GROUP BY c.id
            ORDER BY c.name
        ";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getStatement($clientId, $startDate = null, $endDate = null)
    {
        $sql = "
            SELECT t.*, tt.name as transaction_type_name
            FROM transactions t
            JOIN transaction_types tt ON t.transaction_type_id = tt.id
            WHERE t.client_id = ? AND t.status = 'approved'
        ";
        
        $params = [$clientId];
        
        if ($startDate) {
            $sql .= " AND t.transaction_date >= ?";
            $params[] = $startDate;
        }
        
        if ($endDate) {
            $sql .= " AND t.transaction_date <= ?";
            $params[] = $endDate;
        }
        
        $sql .= " ORDER BY t.transaction_date, t.id";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
}