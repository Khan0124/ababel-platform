<?php
namespace App\Models;

class Exam extends BaseModel {
    protected $table = 'patient_exams';
    protected $fillable = [
        'lab_id', 'patient_id', 'exam_catalog_id', 'employee_id', 
        'exam_date', 'status', 'result', 'notes', 'price', 'created_at', 'updated_at'
    ];
    protected $casts = [
        'lab_id' => 'int',
        'patient_id' => 'int',
        'exam_catalog_id' => 'int',
        'employee_id' => 'int',
        'price' => 'float',
        'exam_date' => 'date'
    ];
    
    /**
     * Get exams by lab
     */
    public function getExamsByLab(int $labId, array $orderBy = ['created_at' => 'DESC']): array {
        return $this->findAll(['lab_id' => $labId], $orderBy);
    }
    
    /**
     * Get recent exams
     */
    public function getRecentExams(int $labId, int $limit = 10): array {
        return $this->findAll(
            ['lab_id' => $labId], 
            ['created_at' => 'DESC'], 
            $limit
        );
    }
    
    /**
     * Get pending exams
     */
    public function getPendingExams(int $labId, int $limit = 10): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND pe.status != 'تم التسليم'
            ORDER BY pe.created_at DESC
            LIMIT ?
        ";
        
        return $this->rawQuery($sql, [$labId, $limit]);
    }
    
    /**
     * Get completed exams
     */
    public function getCompletedExams(int $labId, int $limit = 10): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND pe.status = 'تم التسليم'
            ORDER BY pe.updated_at DESC
            LIMIT ?
        ";
        
        return $this->rawQuery($sql, [$labId, $limit]);
    }
    
    /**
     * Get exam with details
     */
    public function getExamWithDetails(int $examId): ?array {
        $sql = "
            SELECT pe.*, 
                   p.name as patient_name, p.phone as patient_phone, p.age as patient_age, p.gender as patient_gender,
                   ec.name as exam_name, ec.description as exam_description,
                   e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.id = ?
        ";
        
        return $this->rawQuerySingle($sql, [$examId]);
    }
    
    /**
     * Get exams by patient
     */
    public function getExamsByPatient(int $patientId): array {
        $sql = "
            SELECT pe.*, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.patient_id = ?
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$patientId]);
    }
    
    /**
     * Get exams by employee
     */
    public function getExamsByEmployee(int $employeeId, int $labId): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            WHERE pe.employee_id = ? AND pe.lab_id = ?
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$employeeId, $labId]);
    }
    
    /**
     * Get exam statistics
     */
    public function getExamStats(int $labId): array {
        $sql = "
            SELECT 
                COUNT(*) as total_exams,
                COUNT(CASE WHEN status = 'تم التسليم' THEN 1 END) as completed_exams,
                COUNT(CASE WHEN status != 'تم التسليم' THEN 1 END) as pending_exams,
                SUM(price) as total_revenue,
                AVG(price) as avg_price,
                COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN 1 END) as today_exams
            FROM {$this->table}
            WHERE lab_id = ?
        ";
        
        $result = $this->rawQuerySingle($sql, [$labId]);
        return $result ?: [];
    }
    
    /**
     * Get exams by date range
     */
    public function getExamsByDateRange(int $labId, string $startDate, string $endDate): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND DATE(pe.created_at) BETWEEN ? AND ?
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$labId, $startDate, $endDate]);
    }
    
    /**
     * Get exams by status
     */
    public function getExamsByStatus(int $labId, string $status): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND pe.status = ?
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$labId, $status]);
    }
    
    /**
     * Search exams
     */
    public function searchExams(int $labId, string $query): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND (
                p.name LIKE ? OR 
                ec.name LIKE ? OR 
                pe.result LIKE ? OR
                pe.notes LIKE ?
            )
            ORDER BY pe.created_at DESC
        ";
        
        $searchTerm = "%{$query}%";
        return $this->rawQuery($sql, [$labId, $searchTerm, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Update exam status
     */
    public function updateStatus(int $examId, string $status): bool {
        return $this->update($examId, ['status' => $status]);
    }
    
    /**
     * Update exam result
     */
    public function updateResult(int $examId, string $result): bool {
        return $this->update($examId, ['result' => $result, 'status' => 'تم التسليم']);
    }
    
    /**
     * Get exams for today
     */
    public function getTodayExams(int $labId): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND DATE(pe.created_at) = CURDATE()
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$labId]);
    }
    
    /**
     * Get exams for this week
     */
    public function getThisWeekExams(int $labId): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND YEARWEEK(pe.created_at) = YEARWEEK(NOW())
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$labId]);
    }
    
    /**
     * Get exams for this month
     */
    public function getThisMonthExams(int $labId): array {
        $sql = "
            SELECT pe.*, p.name as patient_name, ec.name as exam_name, e.name as employee_name
            FROM {$this->table} pe
            LEFT JOIN patients p ON pe.patient_id = p.id
            LEFT JOIN exam_catalog ec ON pe.exam_catalog_id = ec.id
            LEFT JOIN lab_employees e ON pe.employee_id = e.id
            WHERE pe.lab_id = ? AND MONTH(pe.created_at) = MONTH(NOW()) AND YEAR(pe.created_at) = YEAR(NOW())
            ORDER BY pe.created_at DESC
        ";
        
        return $this->rawQuery($sql, [$labId]);
    }
} 