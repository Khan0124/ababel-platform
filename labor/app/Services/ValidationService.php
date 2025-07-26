<?php

namespace App\Services;

class ValidationService
{
    private $errors = [];
    private $data = [];
    
    public function validate(array $data, array $rules)
    {
        $this->errors = [];
        $this->data = $data;
        
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            $fieldRules = is_string($ruleSet) ? explode('|', $ruleSet) : $ruleSet;
            
            foreach ($fieldRules as $rule) {
                $this->checkRule($field, $value, $rule);
            }
        }
        
        return empty($this->errors);
    }
    
    private function checkRule($field, $value, $rule)
    {
        $params = [];
        
        // Parse rule parameters
        if (strpos($rule, ':') !== false) {
            list($rule, $paramStr) = explode(':', $rule, 2);
            $params = explode(',', $paramStr);
        }
        
        $method = 'validate' . ucfirst($rule);
        
        if (method_exists($this, $method)) {
            $this->$method($field, $value, $params);
        } else {
            throw new \Exception("Validation rule '{$rule}' not found");
        }
    }
    
    public function getErrors()
    {
        return $this->errors;
    }
    
    public function getFirstError()
    {
        return reset($this->errors) ?: null;
    }
    
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    // Validation Rules
    
    private function validateRequired($field, $value)
    {
        if (is_null($value) || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "حقل {$field} مطلوب");
        }
    }
    
    private function validateEmail($field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "البريد الإلكتروني غير صحيح");
        }
    }
    
    private function validateNumeric($field, $value)
    {
        if (!is_numeric($value)) {
            $this->addError($field, "حقل {$field} يجب أن يكون رقم");
        }
    }
    
    private function validateInteger($field, $value)
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            $this->addError($field, "حقل {$field} يجب أن يكون رقم صحيح");
        }
    }
    
    private function validateMin($field, $value, $params)
    {
        $min = $params[0] ?? 0;
        
        if (is_string($value)) {
            if (strlen($value) < $min) {
                $this->addError($field, "حقل {$field} يجب أن يكون {$min} حرف على الأقل");
            }
        } elseif (is_numeric($value)) {
            if ($value < $min) {
                $this->addError($field, "حقل {$field} يجب أن يكون {$min} أو أكثر");
            }
        }
    }
    
    private function validateMax($field, $value, $params)
    {
        $max = $params[0] ?? PHP_INT_MAX;
        
        if (is_string($value)) {
            if (strlen($value) > $max) {
                $this->addError($field, "حقل {$field} يجب ألا يتجاوز {$max} حرف");
            }
        } elseif (is_numeric($value)) {
            if ($value > $max) {
                $this->addError($field, "حقل {$field} يجب ألا يتجاوز {$max}");
            }
        }
    }
    
    private function validateBetween($field, $value, $params)
    {
        $min = $params[0] ?? 0;
        $max = $params[1] ?? PHP_INT_MAX;
        
        if (is_numeric($value)) {
            if ($value < $min || $value > $max) {
                $this->addError($field, "حقل {$field} يجب أن يكون بين {$min} و {$max}");
            }
        }
    }
    
    private function validateIn($field, $value, $params)
    {
        if (!in_array($value, $params)) {
            $this->addError($field, "القيمة المحددة لحقل {$field} غير صحيحة");
        }
    }
    
    private function validateDate($field, $value)
    {
        if (!strtotime($value)) {
            $this->addError($field, "التاريخ غير صحيح");
        }
    }
    
    private function validateBefore($field, $value, $params)
    {
        $date = $params[0] ?? 'now';
        
        if (strtotime($value) >= strtotime($date)) {
            $this->addError($field, "التاريخ يجب أن يكون قبل {$date}");
        }
    }
    
    private function validateAfter($field, $value, $params)
    {
        $date = $params[0] ?? 'now';
        
        if (strtotime($value) <= strtotime($date)) {
            $this->addError($field, "التاريخ يجب أن يكون بعد {$date}");
        }
    }
    
    private function validatePhone($field, $value)
    {
        // Saudi phone number validation
        $pattern = '/^(05|5)(5|0|3|6|4|9|1|8|7)([0-9]{7})$/';
        
        if (!preg_match($pattern, $value)) {
            $this->addError($field, "رقم الهاتف غير صحيح");
        }
    }
    
    private function validateNationalId($field, $value)
    {
        // Saudi national ID validation
        if (!preg_match('/^[12][0-9]{9}$/', $value)) {
            $this->addError($field, "رقم الهوية غير صحيح");
        }
    }
    
    private function validateUnique($field, $value, $params)
    {
        $table = $params[0] ?? null;
        $column = $params[1] ?? $field;
        $except = $params[2] ?? null;
        
        if (!$table) {
            return;
        }
        
        $db = \App\Core\Database::getInstance();
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE {$column} = ?";
        $values = [$value];
        
        if ($except) {
            $sql .= " AND id != ?";
            $values[] = $except;
        }
        
        $stmt = $db->prepare($sql);
        $stmt->execute($values);
        
        if ($stmt->fetch()['count'] > 0) {
            $this->addError($field, "هذا {$field} مستخدم بالفعل");
        }
    }
    
    private function validateConfirmed($field, $value)
    {
        $confirmField = $field . '_confirmation';
        
        if (!isset($this->data[$confirmField]) || $value !== $this->data[$confirmField]) {
            $this->addError($field, "تأكيد {$field} غير متطابق");
        }
    }
    
    private function validateRegex($field, $value, $params)
    {
        $pattern = $params[0] ?? null;
        
        if ($pattern && !preg_match($pattern, $value)) {
            $this->addError($field, "صيغة {$field} غير صحيحة");
        }
    }
    
    private function validateFile($field, $value)
    {
        if (!isset($_FILES[$field]) || $_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, "خطأ في رفع الملف");
        }
    }
    
    private function validateMimes($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return;
        }
        
        $file = $_FILES[$field];
        $mimeType = mime_content_type($file['tmp_name']);
        
        $allowedMimes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
        
        $allowed = [];
        foreach ($params as $ext) {
            if (isset($allowedMimes[$ext])) {
                $allowed[] = $allowedMimes[$ext];
            }
        }
        
        if (!in_array($mimeType, $allowed)) {
            $this->addError($field, "نوع الملف غير مسموح");
        }
    }
    
    private function validateMaxSize($field, $value, $params)
    {
        if (!isset($_FILES[$field])) {
            return;
        }
        
        $maxSize = $params[0] ?? '2M';
        $bytes = $this->convertToBytes($maxSize);
        
        if ($_FILES[$field]['size'] > $bytes) {
            $this->addError($field, "حجم الملف يتجاوز الحد المسموح ({$maxSize})");
        }
    }
    
    private function convertToBytes($size)
    {
        $unit = strtoupper(substr($size, -1));
        $value = (int) substr($size, 0, -1);
        
        switch ($unit) {
            case 'K':
                return $value * 1024;
            case 'M':
                return $value * 1024 * 1024;
            case 'G':
                return $value * 1024 * 1024 * 1024;
            default:
                return $value;
        }
    }
}