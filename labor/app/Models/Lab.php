<?php
namespace App\Models;

class Lab extends BaseModel {
    protected $table = 'labs';
    protected $fillable = [
        'name', 'email', 'phone', 'address', 'logo', 'status', 
        'subscription_type', 'subscription_end_date', 'created_at', 'updated_at'
    ];
    protected $casts = [
        'status' => 'bool',
        'subscription_end_date' => 'date'
    ];
    
    /**
     * Get active labs
     */
    public function getActiveLabs(): array {
        return $this->findAll(['status' => 1], ['name' => 'ASC']);
    }
    
    /**
     * Get labs with expiring subscriptions
     */
    public function getLabsWithExpiringSubscriptions(int $days = 7): array {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE subscription_end_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL ? DAY)
            AND status = 1
            ORDER BY subscription_end_date ASC
        ";
        
        return $this->rawQuery($sql, [$days]);
    }
    
    /**
     * Get lab statistics
     */
    public function getLabStats(int $labId): array {
        $sql = "
            SELECT 
                (SELECT COUNT(*) FROM patients WHERE lab_id = ?) AS total_patients,
                (SELECT COUNT(*) FROM exam_catalog WHERE lab_id = ?) AS total_exams,
                (SELECT COUNT(*) FROM lab_employees WHERE lab_id = ?) AS total_employees,
                (SELECT COUNT(*) FROM patient_exams WHERE lab_id = ?) AS total_results,
                (SELECT COUNT(*) FROM cashbox WHERE lab_id = ?) AS total_transactions,
                (SELECT COUNT(*) FROM patient_exams WHERE lab_id = ? AND status != 'تم التسليم') AS unsubmitted_count
        ";
        
        $result = $this->rawQuerySingle($sql, [$labId, $labId, $labId, $labId, $labId, $labId]);
        return $result ?: [];
    }
    
    /**
     * Update lab status
     */
    public function updateStatus(int $labId, bool $status): bool {
        return $this->update($labId, ['status' => $status]);
    }
    
    /**
     * Get labs by subscription type
     */
    public function getLabsBySubscriptionType(string $type): array {
        return $this->findAll(['subscription_type' => $type], ['name' => 'ASC']);
    }
    
    /**
     * Search labs by name or email
     */
    public function searchLabs(string $query): array {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE name LIKE ? OR email LIKE ? OR phone LIKE ?
            ORDER BY name ASC
        ";
        
        $searchTerm = "%{$query}%";
        return $this->rawQuery($sql, [$searchTerm, $searchTerm, $searchTerm]);
    }
} 