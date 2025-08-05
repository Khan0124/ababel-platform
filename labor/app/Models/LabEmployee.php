<?php
namespace App\Models;

class LabEmployee extends BaseModel {
    protected $table = 'lab_employees';
    protected $fillable = [
        'lab_id', 'name', 'username', 'password', 'email', 'phone', 
        'role', 'status', 'created_at', 'updated_at'
    ];
    protected $casts = [
        'lab_id' => 'int',
        'status' => 'bool'
    ];
    
    /**
     * Find employee by username and lab
     */
    public function findByUsernameAndLab(string $username, int $labId): ?array {
        $sql = "SELECT * FROM {$this->table} WHERE username = ? AND lab_id = ?";
        return $this->rawQuerySingle($sql, [$username, $labId]);
    }
    
    /**
     * Get employees by lab
     */
    public function getEmployeesByLab(int $labId, array $orderBy = ['name' => 'ASC']): array {
        return $this->findAll(['lab_id' => $labId], $orderBy);
    }
    
    /**
     * Get employees by role
     */
    public function getEmployeesByRole(int $labId, string $role): array {
        return $this->findAll(['lab_id' => $labId, 'role' => $role], ['name' => 'ASC']);
    }
    
    /**
     * Get active employees
     */
    public function getActiveEmployees(int $labId): array {
        return $this->findAll(['lab_id' => $labId, 'status' => 1], ['name' => 'ASC']);
    }
    
    /**
     * Search employees
     */
    public function searchEmployees(int $labId, string $query): array {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE lab_id = ? AND (name LIKE ? OR username LIKE ? OR email LIKE ?)
            ORDER BY name ASC
        ";
        
        $searchTerm = "%{$query}%";
        return $this->rawQuery($sql, [$labId, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Get employee statistics
     */
    public function getEmployeeStats(int $labId): array {
        $sql = "
            SELECT 
                COUNT(*) as total_employees,
                COUNT(CASE WHEN status = 1 THEN 1 END) as active_employees,
                COUNT(CASE WHEN status = 0 THEN 1 END) as inactive_employees,
                COUNT(CASE WHEN role = 'مدير' THEN 1 END) as managers_count,
                COUNT(CASE WHEN role = 'فني' THEN 1 END) as technicians_count,
                COUNT(CASE WHEN role = 'محاسب' THEN 1 END) as accountants_count
            FROM {$this->table}
            WHERE lab_id = ?
        ";
        
        $result = $this->rawQuerySingle($sql, [$labId]);
        return $result ?: [];
    }
    
    /**
     * Update employee status
     */
    public function updateStatus(int $employeeId, bool $status): bool {
        return $this->update($employeeId, ['status' => $status]);
    }
    
    /**
     * Check if username exists in lab
     */
    public function usernameExists(string $username, int $labId, ?int $excludeId = null): bool {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = ? AND lab_id = ?";
        $params = [$username, $labId];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->rawQuerySingle($sql, $params);
        return $result && $result['count'] > 0;
    }
    
    /**
     * Get employee with attendance
     */
    public function getEmployeeWithAttendance(int $employeeId, string $date = null): ?array {
        $date = $date ?: date('Y-m-d');
        
        $sql = "
            SELECT e.*, 
                   a.check_in_time,
                   a.check_out_time,
                   a.total_hours,
                   a.status as attendance_status
            FROM {$this->table} e
            LEFT JOIN employee_attendance a ON e.id = a.employee_id AND DATE(a.date) = ?
            WHERE e.id = ?
        ";
        
        return $this->rawQuerySingle($sql, [$date, $employeeId]);
    }
    
    /**
     * Get employees with recent activity
     */
    public function getEmployeesWithRecentActivity(int $labId, int $days = 7): array {
        $sql = "
            SELECT e.*, 
                   COUNT(pe.id) as exams_processed,
                   MAX(pe.created_at) as last_activity
            FROM {$this->table} e
            LEFT JOIN patient_exams pe ON e.id = pe.employee_id 
                AND pe.created_at >= DATE_SUB(NOW(), INTERVAL ? DAY)
            WHERE e.lab_id = ?
            GROUP BY e.id
            ORDER BY last_activity DESC, e.name ASC
        ";
        
        return $this->rawQuery($sql, [$days, $labId]);
    }
} 