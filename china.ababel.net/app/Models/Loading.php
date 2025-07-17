<?php
// app/Models/Loading.php
namespace App\Models;

use App\Core\Model;

class Loading extends Model
{
    protected $table = 'loadings';
    
    /**
     * Get loading with client details
     */
    public function getWithDetails($id)
    {
        $sql = "SELECT l.*, 
                c.name as client_name_db, 
                c.name_ar as client_name_ar,
                c.phone as client_phone,
                c.balance_rmb,
                c.balance_usd,
                u1.full_name as created_by_name,
                u2.full_name as updated_by_name
                FROM {$this->table} l
                LEFT JOIN clients c ON l.client_id = c.id
                LEFT JOIN users u1 ON l.created_by = u1.id
                LEFT JOIN users u2 ON l.updated_by = u2.id
                WHERE l.id = ?";
        
        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }
    
    /**
     * Get filtered loadings
     */
    public function getFiltered($filters = [])
    {
        $sql = "SELECT l.*, 
                c.name as client_name_db, 
                c.name_ar as client_name_ar,
                u.full_name as created_by_name
                FROM {$this->table} l
                LEFT JOIN clients c ON l.client_id = c.id
                LEFT JOIN users u ON l.created_by = u.id
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND l.shipping_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND l.shipping_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['client_code'])) {
            $sql .= " AND l.client_code LIKE ?";
            $params[] = '%' . $filters['client_code'] . '%';
        }
        
        if (!empty($filters['container_no'])) {
            $sql .= " AND l.container_no LIKE ?";
            $params[] = '%' . $filters['container_no'] . '%';
        }
        
        if (!empty($filters['office'])) {
            $sql .= " AND l.office = ?";
            $params[] = $filters['office'];
        }
        
        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        
        $sql .= " ORDER BY l.shipping_date DESC, l.id DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get loadings by office
     */
    public function getByOffice($office, $status = null)
    {
        $sql = "SELECT l.*, 
                c.name as client_name_db, 
                c.name_ar as client_name_ar
                FROM {$this->table} l
                LEFT JOIN clients c ON l.client_id = c.id
                WHERE l.office = ?";
        
        $params = [$office];
        
        if ($status) {
            $sql .= " AND l.status = ?";
            $params[] = $status;
        }
        
        $sql .= " ORDER BY l.shipping_date DESC";
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Get statistics
     */
    public function getStatistics($filters = [])
    {
        $sql = "SELECT 
                COUNT(*) as total_containers,
                SUM(cartons_count) as total_cartons,
                SUM(purchase_amount) as total_purchase,
                SUM(commission_amount) as total_commission,
                SUM(total_amount) as total_amount,
                SUM(shipping_usd) as total_shipping,
                SUM(total_with_shipping) as grand_total,
                COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_count,
                COUNT(CASE WHEN status = 'shipped' THEN 1 END) as shipped_count,
                COUNT(CASE WHEN status = 'arrived' THEN 1 END) as arrived_count,
                COUNT(CASE WHEN status = 'cleared' THEN 1 END) as cleared_count
                FROM {$this->table}
                WHERE 1=1";
        
        $params = [];
        
        if (!empty($filters['date_from'])) {
            $sql .= " AND shipping_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $sql .= " AND shipping_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        if (!empty($filters['office'])) {
            $sql .= " AND office = ?";
            $params[] = $filters['office'];
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Get recent loadings
     */
    public function getRecent($limit = 10)
    {
        $sql = "SELECT l.*, 
                c.name as client_name
                FROM {$this->table} l
                LEFT JOIN clients c ON l.client_id = c.id
                ORDER BY l.created_at DESC
                LIMIT ?";
        
        $stmt = $this->db->query($sql, [$limit]);
        return $stmt->fetchAll();
    }
    
    /**
     * Check if container exists
     */
    public function containerExists($containerNo, $excludeId = null)
    {
        $sql = "SELECT id FROM {$this->table} WHERE container_no = ?";
        $params = [$containerNo];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch() !== false;
    }
}