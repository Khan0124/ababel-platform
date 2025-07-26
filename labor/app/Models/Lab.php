<?php

namespace App\Models;

use App\Core\Model;

class Lab extends Model
{
    protected $table = 'labs';
    protected $fillable = [
        'name', 'email', 'password', 'phone', 'address', 
        'city', 'logo', 'subscription_type', 'subscription_end', 
        'is_active', 'settings'
    ];
    protected $hidden = ['password'];
    
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    public function getEmployees()
    {
        return Employee::all(['lab_id' => $this->id]);
    }
    
    public function getActiveEmployees()
    {
        return Employee::all(['lab_id' => $this->id, 'status' => 'نشط']);
    }
    
    public function getPatients($limit = null)
    {
        $conditions = ['lab_id' => $this->id];
        return Patient::all($conditions, 'created_at DESC', $limit);
    }
    
    public function getExams()
    {
        return Exam::all(['lab_id' => $this->id]);
    }
    
    public function getTodayTransactions()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM transactions 
            WHERE lab_id = ? AND DATE(created_at) = CURDATE()
            ORDER BY created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    public function getMonthlyRevenue()
    {
        $stmt = $this->db->prepare("
            SELECT SUM(amount) as total 
            FROM transactions 
            WHERE lab_id = ? 
            AND type = 'income' 
            AND MONTH(created_at) = MONTH(CURRENT_DATE())
            AND YEAR(created_at) = YEAR(CURRENT_DATE())
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function isSubscriptionActive()
    {
        if (!$this->subscription_end) {
            return false;
        }
        return strtotime($this->subscription_end) > time();
    }
    
    public function getDaysUntilExpiry()
    {
        if (!$this->subscription_end) {
            return 0;
        }
        $diff = strtotime($this->subscription_end) - time();
        return max(0, ceil($diff / 86400));
    }
    
    public function updateSettings($settings)
    {
        $this->settings = json_encode($settings);
        return $this->save();
    }
    
    public function getSettings()
    {
        return json_decode($this->settings, true) ?? [];
    }
}