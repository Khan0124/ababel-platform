<?php

namespace App\Models;

use App\Core\Model;

class Employee extends Model
{
    protected $table = 'lab_employees';
    protected $fillable = [
        'lab_id', 'name', 'email', 'password', 'phone',
        'role', 'permissions', 'salary', 'hire_date', 'status'
    ];
    protected $hidden = ['password'];
    
    public static function authenticate($email, $password)
    {
        $employee = self::findBy('email', $email);
        
        if ($employee && password_verify($password, $employee->password)) {
            if ($employee->status !== 'نشط') {
                return ['success' => false, 'message' => 'الحساب غير نشط'];
            }
            return ['success' => true, 'employee' => $employee];
        }
        
        return ['success' => false, 'message' => 'بيانات الدخول غير صحيحة'];
    }
    
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }
    
    public function getLab()
    {
        return Lab::find($this->lab_id);
    }
    
    public function hasPermission($permission)
    {
        $permissions = json_decode($this->permissions, true) ?? [];
        return in_array($permission, $permissions) || in_array('*', $permissions);
    }
    
    public function getAttendanceToday()
    {
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE employee_id = ? AND DATE(check_in) = CURDATE()
            LIMIT 1
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch();
    }
    
    public function checkIn()
    {
        $stmt = $this->db->prepare("
            INSERT INTO attendance (employee_id, lab_id, check_in, date) 
            VALUES (?, ?, NOW(), CURDATE())
        ");
        return $stmt->execute([$this->id, $this->lab_id]);
    }
    
    public function checkOut()
    {
        $stmt = $this->db->prepare("
            UPDATE attendance 
            SET check_out = NOW() 
            WHERE employee_id = ? AND DATE(check_in) = CURDATE() AND check_out IS NULL
        ");
        return $stmt->execute([$this->id]);
    }
    
    public function getMonthlyAttendance($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $stmt = $this->db->prepare("
            SELECT * FROM attendance 
            WHERE employee_id = ? 
            AND MONTH(date) = ? 
            AND YEAR(date) = ?
            ORDER BY date DESC
        ");
        $stmt->execute([$this->id, $month, $year]);
        return $stmt->fetchAll();
    }
    
    public function logActivity($action, $description = null)
    {
        $stmt = $this->db->prepare("
            INSERT INTO employee_activity_logs (employee_id, lab_id, action, description, ip_address, created_at) 
            VALUES (?, ?, ?, ?, ?, NOW())
        ");
        return $stmt->execute([
            $this->id,
            $this->lab_id,
            $action,
            $description,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);
    }
}