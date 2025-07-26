<?php

namespace App\Models;

use App\Core\Model;

class Exam extends Model
{
    protected $table = 'exams';
    protected $fillable = [
        'lab_id', 'category_id', 'name', 'code', 'description',
        'price', 'cost', 'normal_range', 'unit', 'sample_type',
        'preparation_instructions', 'turnaround_time', 'is_active'
    ];
    
    public function getLab()
    {
        return Lab::find($this->lab_id);
    }
    
    public function getCategory()
    {
        return ExamCategory::find($this->category_id);
    }
    
    public function getProfitMargin()
    {
        if ($this->cost <= 0) {
            return 100;
        }
        return round((($this->price - $this->cost) / $this->price) * 100, 2);
    }
    
    public function getResultsCount()
    {
        return ExamResult::count(['exam_id' => $this->id]);
    }
    
    public function getMonthlyRevenue($month = null, $year = null)
    {
        $month = $month ?? date('m');
        $year = $year ?? date('Y');
        
        $stmt = $this->db->prepare("
            SELECT SUM(price) as revenue
            FROM exam_results
            WHERE exam_id = ?
            AND MONTH(created_at) = ?
            AND YEAR(created_at) = ?
        ");
        $stmt->execute([$this->id, $month, $year]);
        return $stmt->fetch()['revenue'] ?? 0;
    }
    
    public function getAverageProcessingTime()
    {
        $stmt = $this->db->prepare("
            SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, result_date)) as avg_hours
            FROM exam_results
            WHERE exam_id = ? AND result_date IS NOT NULL
        ");
        $stmt->execute([$this->id]);
        return round($stmt->fetch()['avg_hours'] ?? 0, 1);
    }
    
    public function isAvailable()
    {
        // Check if exam is active and has required supplies in stock
        if (!$this->is_active) {
            return false;
        }
        
        // Check stock requirements
        $stmt = $this->db->prepare("
            SELECT COUNT(*) as out_of_stock
            FROM exam_supplies es
            JOIN stock_items si ON es.stock_item_id = si.id
            WHERE es.exam_id = ? AND si.current_quantity < es.required_quantity
        ");
        $stmt->execute([$this->id]);
        
        return $stmt->fetch()['out_of_stock'] == 0;
    }
}