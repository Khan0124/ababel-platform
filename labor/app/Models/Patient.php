<?php
namespace App\Models;

class Patient extends BaseModel {
    protected $table = 'patients';
    protected $fillable = [
        'lab_id', 'name', 'phone', 'email', 'age', 'gender', 
        'address', 'insurance_company', 'insurance_number', 'created_at', 'updated_at'
    ];
    protected $casts = [
        'age' => 'int',
        'lab_id' => 'int'
    ];
    
    /**
     * Get patients by lab
     */
    public function getPatientsByLab(int $labId, array $orderBy = ['name' => 'ASC']): array {
        return $this->findAll(['lab_id' => $labId], $orderBy);
    }
    
    /**
     * Search patients by name or phone
     */
    public function searchPatients(int $labId, string $query): array {
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE lab_id = ? AND (name LIKE ? OR phone LIKE ? OR insurance_number LIKE ?)
            ORDER BY name ASC
        ";
        
        $searchTerm = "%{$query}%";
        return $this->rawQuery($sql, [$labId, $searchTerm, $searchTerm, $searchTerm]);
    }
    
    /**
     * Get patient with exams
     */
    public function getPatientWithExams(int $patientId): ?array {
        $sql = "
            SELECT p.*, 
                   COUNT(pe.id) as total_exams,
                   COUNT(CASE WHEN pe.status = 'تم التسليم' THEN 1 END) as completed_exams,
                   COUNT(CASE WHEN pe.status != 'تم التسليم' THEN 1 END) as pending_exams
            FROM {$this->table} p
            LEFT JOIN patient_exams pe ON p.id = pe.patient_id
            WHERE p.id = ?
            GROUP BY p.id
        ";
        
        return $this->rawQuerySingle($sql, [$patientId]);
    }
    
    /**
     * Get patients with pending exams
     */
    public function getPatientsWithPendingExams(int $labId): array {
        $sql = "
            SELECT DISTINCT p.*, COUNT(pe.id) as pending_count
            FROM {$this->table} p
            INNER JOIN patient_exams pe ON p.id = pe.patient_id
            WHERE p.lab_id = ? AND pe.status != 'تم التسليم'
            GROUP BY p.id
            ORDER BY pending_count DESC, p.name ASC
        ";
        
        return $this->rawQuery($sql, [$labId]);
    }
    
    /**
     * Get patient statistics
     */
    public function getPatientStats(int $labId): array {
        $sql = "
            SELECT 
                COUNT(*) as total_patients,
                COUNT(CASE WHEN gender = 'ذكر' THEN 1 END) as male_count,
                COUNT(CASE WHEN gender = 'أنثى' THEN 1 END) as female_count,
                AVG(age) as avg_age,
                COUNT(CASE WHEN insurance_company IS NOT NULL AND insurance_company != '' THEN 1 END) as insured_count
            FROM {$this->table}
            WHERE lab_id = ?
        ";
        
        $result = $this->rawQuerySingle($sql, [$labId]);
        return $result ?: [];
    }
    
    /**
     * Get patients by age group
     */
    public function getPatientsByAgeGroup(int $labId): array {
        $sql = "
            SELECT 
                CASE 
                    WHEN age < 18 THEN 'أطفال'
                    WHEN age BETWEEN 18 AND 30 THEN 'شباب'
                    WHEN age BETWEEN 31 AND 50 THEN 'وسط'
                    WHEN age BETWEEN 51 AND 65 THEN 'كبار'
                    ELSE 'مسنين'
                END as age_group,
                COUNT(*) as count
            FROM {$this->table}
            WHERE lab_id = ?
            GROUP BY age_group
            ORDER BY count DESC
        ";
        
        return $this->rawQuery($sql, [$labId]);
    }
    
    /**
     * Get patients by insurance company
     */
    public function getPatientsByInsurance(int $labId): array {
        $sql = "
            SELECT 
                insurance_company,
                COUNT(*) as count
            FROM {$this->table}
            WHERE lab_id = ? AND insurance_company IS NOT NULL AND insurance_company != ''
            GROUP BY insurance_company
            ORDER BY count DESC
        ";
        
        return $this->rawQuery($sql, [$labId]);
    }
    
    /**
     * Get recent patients
     */
    public function getRecentPatients(int $labId, int $limit = 10): array {
        return $this->findAll(
            ['lab_id' => $labId], 
            ['created_at' => 'DESC'], 
            $limit
        );
    }
} 