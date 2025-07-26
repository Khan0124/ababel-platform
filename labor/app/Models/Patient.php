<?php

namespace App\Models;

use App\Core\Model;

class Patient extends Model
{
    protected $table = 'patients';
    protected $fillable = [
        'lab_id', 'name', 'phone', 'email', 'national_id',
        'date_of_birth', 'gender', 'address', 'blood_type',
        'medical_history', 'notes', 'created_by'
    ];
    
    public function getLab()
    {
        return Lab::find($this->lab_id);
    }
    
    public function getAge()
    {
        if (!$this->date_of_birth) {
            return null;
        }
        $dob = new \DateTime($this->date_of_birth);
        $now = new \DateTime();
        return $dob->diff($now)->y;
    }
    
    public function getExamResults($limit = null)
    {
        $sql = "SELECT er.*, e.name as exam_name, e.price, emp.name as performed_by_name 
                FROM exam_results er
                JOIN exams e ON er.exam_id = e.id
                LEFT JOIN lab_employees emp ON er.performed_by = emp.id
                WHERE er.patient_id = ?
                ORDER BY er.created_at DESC";
        
        if ($limit) {
            $sql .= " LIMIT " . (int)$limit;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    public function getPendingExams()
    {
        $stmt = $this->db->prepare("
            SELECT pe.*, e.name as exam_name, e.normal_range, e.unit
            FROM patient_exams pe
            JOIN exams e ON pe.exam_id = e.id
            WHERE pe.patient_id = ? AND pe.status = 'pending'
            ORDER BY pe.created_at DESC
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetchAll();
    }
    
    public function getTotalSpent()
    {
        $stmt = $this->db->prepare("
            SELECT SUM(total_amount) as total
            FROM invoices
            WHERE patient_id = ? AND status = 'paid'
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch()['total'] ?? 0;
    }
    
    public function getLastVisit()
    {
        $stmt = $this->db->prepare("
            SELECT MAX(created_at) as last_visit
            FROM exam_results
            WHERE patient_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch()['last_visit'];
    }
    
    public function getVisitCount()
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(DISTINCT DATE(created_at)) as visits
            FROM exam_results
            WHERE patient_id = ?
        ");
        $stmt->execute([$this->id]);
        return $stmt->fetch()['visits'] ?? 0;
    }
    
    public function addMedicalHistory($entry)
    {
        $history = json_decode($this->medical_history, true) ?? [];
        $history[] = [
            'date' => date('Y-m-d'),
            'entry' => $entry,
            'added_by' => $_SESSION['employee_id'] ?? $_SESSION['admin_id'] ?? null
        ];
        $this->medical_history = json_encode($history);
        return $this->save();
    }
    
    public function getMedicalHistoryArray()
    {
        return json_decode($this->medical_history, true) ?? [];
    }
}