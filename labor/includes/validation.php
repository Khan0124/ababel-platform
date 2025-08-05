<?php
/**
 * Input Validation and Sanitization Helper
 */

class Validator {
    
    private $errors = [];
    private $data = [];
    
    /**
     * Validate and sanitize input data
     */
    public function validate($data, $rules) {
        $this->errors = [];
        $this->data = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;
            $this->validateField($field, $value, $rule);
        }
        
        return empty($this->errors);
    }
    
    /**
     * Get validation errors
     */
    public function getErrors() {
        return $this->errors;
    }
    
    /**
     * Get sanitized data
     */
    public function getData() {
        return $this->data;
    }
    
    /**
     * Validate a single field
     */
    private function validateField($field, $value, $rules) {
        $rules = is_string($rules) ? explode('|', $rules) : $rules;
        
        foreach ($rules as $rule) {
            $parameters = [];
            
            // Parse rule parameters
            if (strpos($rule, ':') !== false) {
                list($rule, $paramStr) = explode(':', $rule, 2);
                $parameters = explode(',', $paramStr);
            }
            
            // Apply validation rule
            switch ($rule) {
                case 'required':
                    if ($this->isEmpty($value)) {
                        $this->addError($field, "حقل $field مطلوب");
                        return;
                    }
                    break;
                    
                case 'email':
                    if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $this->addError($field, "البريد الإلكتروني غير صحيح");
                        return;
                    }
                    break;
                    
                case 'numeric':
                    if (!$this->isEmpty($value) && !is_numeric($value)) {
                        $this->addError($field, "يجب أن يكون $field رقماً");
                        return;
                    }
                    break;
                    
                case 'integer':
                    if (!$this->isEmpty($value) && !filter_var($value, FILTER_VALIDATE_INT)) {
                        $this->addError($field, "يجب أن يكون $field رقماً صحيحاً");
                        return;
                    }
                    break;
                    
                case 'min':
                    $min = $parameters[0] ?? 0;
                    if (!$this->isEmpty($value) && (is_numeric($value) ? $value < $min : strlen($value) < $min)) {
                        $this->addError($field, "يجب أن يكون $field أكبر من أو يساوي $min");
                        return;
                    }
                    break;
                    
                case 'max':
                    $max = $parameters[0] ?? PHP_INT_MAX;
                    if (!$this->isEmpty($value) && (is_numeric($value) ? $value > $max : strlen($value) > $max)) {
                        $this->addError($field, "يجب أن يكون $field أقل من أو يساوي $max");
                        return;
                    }
                    break;
                    
                case 'in':
                    if (!$this->isEmpty($value) && !in_array($value, $parameters)) {
                        $this->addError($field, "قيمة $field غير صحيحة");
                        return;
                    }
                    break;
                    
                case 'date':
                    if (!$this->isEmpty($value) && !$this->isValidDate($value)) {
                        $this->addError($field, "تاريخ غير صحيح");
                        return;
                    }
                    break;
                    
                case 'alpha':
                    if (!$this->isEmpty($value) && !ctype_alpha($value)) {
                        $this->addError($field, "يجب أن يحتوي $field على أحرف فقط");
                        return;
                    }
                    break;
                    
                case 'alphanumeric':
                    if (!$this->isEmpty($value) && !ctype_alnum($value)) {
                        $this->addError($field, "يجب أن يحتوي $field على أحرف وأرقام فقط");
                        return;
                    }
                    break;
                    
                case 'phone':
                    if (!$this->isEmpty($value) && !$this->isValidPhone($value)) {
                        $this->addError($field, "رقم الهاتف غير صحيح");
                        return;
                    }
                    break;
                    
                case 'password':
                    if (!$this->isEmpty($value) && strlen($value) < 8) {
                        $this->addError($field, "كلمة المرور يجب أن تكون 8 أحرف على الأقل");
                        return;
                    }
                    break;
                    
                case 'decimal':
                    $decimals = $parameters[0] ?? 2;
                    if (!$this->isEmpty($value) && !$this->isValidDecimal($value, $decimals)) {
                        $this->addError($field, "يجب أن يكون $field رقماً عشرياً صحيحاً");
                        return;
                    }
                    break;
            }
        }
        
        // Sanitize and store the value
        if (!$this->isEmpty($value)) {
            $this->data[$field] = $this->sanitize($value);
        } else {
            $this->data[$field] = null;
        }
    }
    
    /**
     * Check if value is empty
     */
    private function isEmpty($value) {
        return $value === null || $value === '' || (is_array($value) && empty($value));
    }
    
    /**
     * Check if date is valid
     */
    private function isValidDate($date) {
        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }
    
    /**
     * Check if phone number is valid
     */
    private function isValidPhone($phone) {
        // Sudan phone number format
        return preg_match('/^(\+?249|0)?[19]\d{8}$/', $phone);
    }
    
    /**
     * Check if decimal is valid
     */
    private function isValidDecimal($value, $decimals) {
        return preg_match('/^\d+(\.\d{1,' . $decimals . '})?$/', $value);
    }
    
    /**
     * Sanitize input value
     */
    private function sanitize($value) {
        if (is_array($value)) {
            return array_map([$this, 'sanitize'], $value);
        }
        
        // Remove extra whitespace
        $value = trim($value);
        
        // Convert special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
        
        // Remove any null bytes
        $value = str_replace(chr(0), '', $value);
        
        return $value;
    }
    
    /**
     * Add validation error
     */
    private function addError($field, $message) {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }
        $this->errors[$field][] = $message;
    }
    
    /**
     * Static helper methods
     */
    
    public static function sanitizeInput($input) {
        if (is_array($input)) {
            return array_map([self::class, 'sanitizeInput'], $input);
        }
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }
    
    public static function sanitizeInt($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
    }
    
    public static function sanitizeFloat($input) {
        return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }
    
    public static function sanitizeEmail($input) {
        return filter_var($input, FILTER_SANITIZE_EMAIL);
    }
    
    public static function sanitizeUrl($input) {
        return filter_var($input, FILTER_SANITIZE_URL);
    }
    
    public static function validateArabicText($input) {
        // Allow Arabic letters, spaces, and common punctuation
        return preg_match('/^[\p{Arabic}\s\-_,.!?]+$/u', $input);
    }
    
    public static function validateLabCode($code) {
        // Lab codes should be alphanumeric with optional dash
        return preg_match('/^[A-Z0-9\-]{3,20}$/', $code);
    }
    
    public static function validatePatientCode($code) {
        // Patient codes format: P-12345
        return preg_match('/^P-\d{5,}$/', $code);
    }
}

/**
 * Helper function for quick validation
 */
function validate($data, $rules) {
    $validator = new Validator();
    $isValid = $validator->validate($data, $rules);
    
    return [
        'valid' => $isValid,
        'data' => $validator->getData(),
        'errors' => $validator->getErrors()
    ];
}
?>