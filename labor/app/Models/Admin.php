<?php

namespace App\Models;

use App\Core\Model;

class Admin extends Model
{
    protected $table = 'admins';
    protected $fillable = ['name', 'email', 'password', 'role', 'is_active'];
    protected $hidden = ['password'];
    
    public static function authenticate($email, $password)
    {
        $admin = self::findBy('email', $email);
        
        if ($admin && password_verify($password, $admin->password)) {
            if (!$admin->is_active) {
                return ['success' => false, 'message' => 'الحساب معطل'];
            }
            return ['success' => true, 'admin' => $admin];
        }
        
        return ['success' => false, 'message' => 'بيانات الدخول غير صحيحة'];
    }
    
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    public function getActivityLogs($limit = 50)
    {
        $stmt = $this->db->prepare("
            SELECT * FROM activity_logs 
            WHERE admin_id = ? 
            ORDER BY created_at DESC 
            LIMIT ?
        ");
        $stmt->execute([$this->id, $limit]);
        return $stmt->fetchAll();
    }
    
    public function logActivity($action, $description = null, $ip_address = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO activity_logs (admin_id, action, description, ip_address, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $this->id,
            $action,
            $description,
            $ip_address ?? $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}