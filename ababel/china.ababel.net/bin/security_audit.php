#!/usr/bin/env php
<?php
/**
 * Security Audit Script
 * Performs comprehensive security checks on the application
 */

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Database;
use App\Core\SecurityManager;

class SecurityAudit
{
    private $db;
    private $security;
    private $issues = [];
    private $warnings = [];
    private $recommendations = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->security = SecurityManager::getInstance();
    }
    
    /**
     * Run comprehensive security audit
     */
    public function run()
    {
        echo "🔍 Starting Security Audit...\n\n";
        
        $this->checkDatabaseSecurity();
        $this->checkFilePermissions();
        $this->checkConfigurationSecurity();
        $this->checkSessionSecurity();
        $this->checkPasswordSecurity();
        $this->checkInputValidation();
        $this->checkErrorHandling();
        $this->checkLoggingSecurity();
        $this->checkRateLimiting();
        $this->checkCSRFProtection();
        $this->checkSQLInjectionProtection();
        $this->checkXSSProtection();
        $this->checkFileUploadSecurity();
        $this->checkAPISecurity();
        
        $this->generateReport();
    }
    
    /**
     * Check database security
     */
    private function checkDatabaseSecurity()
    {
        echo "📊 Checking Database Security...\n";
        
        // Check if database credentials are in environment variables
        if (empty($_ENV['DB_PASSWORD']) || $_ENV['DB_PASSWORD'] === 'your_secure_password_here') {
            $this->issues[] = "Database password is not properly configured in environment variables";
        }
        
        // Check for hardcoded credentials
        $configFiles = [
            __DIR__ . '/../config/database.php',
            __DIR__ . '/../config/app.php'
        ];
        
        foreach ($configFiles as $file) {
            if (file_exists($file)) {
                $content = file_get_contents($file);
                if (preg_match('/password.*=.*[\'"]\w+[\'"]/', $content)) {
                    $this->issues[] = "Hardcoded credentials found in $file";
                }
            }
        }
        
        // Check database connection security
        try {
            $stmt = $this->db->query("SHOW VARIABLES LIKE 'ssl_cipher'");
            $result = $stmt->fetch();
            if (empty($result['Value'])) {
                $this->warnings[] = "Database connection is not using SSL/TLS encryption";
            }
        } catch (Exception $e) {
            $this->warnings[] = "Could not verify database SSL configuration";
        }
        
        echo "✅ Database security check completed\n";
    }
    
    /**
     * Check file permissions
     */
    private function checkFilePermissions()
    {
        echo "📁 Checking File Permissions...\n";
        
        $criticalFiles = [
            __DIR__ . '/../config/database.php' => 0644,
            __DIR__ . '/../config/app.php' => 0644,
            __DIR__ . '/../.env' => 0600,
        ];
        
        foreach ($criticalFiles as $file => $expectedPerms) {
            if (file_exists($file)) {
                $perms = fileperms($file) & 0777;
                if ($perms !== $expectedPerms) {
                    $this->issues[] = "Incorrect permissions on $file (current: " . decoct($perms) . ", expected: " . decoct($expectedPerms) . ")";
                }
            }
        }
        
        // Check directory permissions
        $directories = [
            __DIR__ . '/../storage/logs' => 0755,
            __DIR__ . '/../storage/cache' => 0755,
            __DIR__ . '/../storage/exports' => 0755,
        ];
        
        foreach ($directories as $dir => $expectedPerms) {
            if (is_dir($dir)) {
                $perms = fileperms($dir) & 0777;
                if ($perms !== $expectedPerms) {
                    $this->warnings[] = "Incorrect permissions on directory $dir (current: " . decoct($perms) . ", expected: " . decoct($expectedPerms) . ")";
                }
            }
        }
        
        echo "✅ File permissions check completed\n";
    }
    
    /**
     * Check configuration security
     */
    private function checkConfigurationSecurity()
    {
        echo "⚙️ Checking Configuration Security...\n";
        
        // Check if error display is disabled in production
        if (ini_get('display_errors') && $_ENV['APP_ENV'] === 'production') {
            $this->issues[] = "Error display is enabled in production environment";
        }
        
        // Check if session security is properly configured
        if (!ini_get('session.cookie_httponly')) {
            $this->issues[] = "Session cookies are not HttpOnly";
        }
        
        if (!ini_get('session.cookie_secure') && $_ENV['APP_ENV'] === 'production') {
            $this->warnings[] = "Session cookies are not secure in production";
        }
        
        // Check for weak session configuration
        if (ini_get('session.gc_maxlifetime') > 3600) {
            $this->warnings[] = "Session lifetime is too long (current: " . ini_get('session.gc_maxlifetime') . " seconds)";
        }
        
        echo "✅ Configuration security check completed\n";
    }
    
    /**
     * Check session security
     */
    private function checkSessionSecurity()
    {
        echo "🔐 Checking Session Security...\n";
        
        // Check session configuration
        if (!ini_get('session.use_strict_mode')) {
            $this->warnings[] = "Session strict mode is not enabled";
        }
        
        if (ini_get('session.cookie_samesite') !== 'Strict') {
            $this->warnings[] = "Session SameSite is not set to Strict";
        }
        
        echo "✅ Session security check completed\n";
    }
    
    /**
     * Check password security
     */
    private function checkPasswordSecurity()
    {
        echo "🔑 Checking Password Security...\n";
        
        // Check password hashing algorithm
        try {
            $stmt = $this->db->query("SELECT password FROM users LIMIT 1");
            $user = $stmt->fetch();
            
            if ($user && $user['password']) {
                if (!password_get_info($user['password'])) {
                    $this->issues[] = "Passwords are not properly hashed";
                } else {
                    $info = password_get_info($user['password']);
                    if ($info['algoName'] === 'bcrypt' && $info['options']['cost'] < 12) {
                        $this->warnings[] = "Bcrypt cost factor is too low (current: {$info['options']['cost']}, recommended: 12+)";
                    }
                }
            }
        } catch (Exception $e) {
            $this->warnings[] = "Could not verify password hashing: " . $e->getMessage();
        }
        
        echo "✅ Password security check completed\n";
    }
    
    /**
     * Check input validation
     */
    private function checkInputValidation()
    {
        echo "✅ Checking Input Validation...\n";
        
        // Check if input sanitization is implemented
        $files = glob(__DIR__ . '/../app/Controllers/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (strpos($content, '$_POST') !== false && strpos($content, 'sanitize') === false) {
                $this->warnings[] = "Potential unsanitized input in $file";
            }
        }
        
        echo "✅ Input validation check completed\n";
    }
    
    /**
     * Check error handling
     */
    private function checkErrorHandling()
    {
        echo "⚠️ Checking Error Handling...\n";
        
        // Check for die() statements that might expose information
        $files = glob(__DIR__ . '/../app/**/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/die\s*\(\s*[\'"]\s*[^$]/', $content)) {
                $this->warnings[] = "Potential information disclosure in $file (die() statement)";
            }
        }
        
        echo "✅ Error handling check completed\n";
    }
    
    /**
     * Check logging security
     */
    private function checkLoggingSecurity()
    {
        echo "📝 Checking Logging Security...\n";
        
        // Check if log files are writable
        $logDir = __DIR__ . '/../storage/logs';
        if (!is_dir($logDir)) {
            $this->issues[] = "Log directory does not exist";
        } elseif (!is_writable($logDir)) {
            $this->issues[] = "Log directory is not writable";
        }
        
        // Check log file permissions
        $logFiles = glob($logDir . '/*.log');
        foreach ($logFiles as $file) {
            $perms = fileperms($file) & 0777;
            if ($perms !== 0644) {
                $this->warnings[] = "Incorrect permissions on log file $file";
            }
        }
        
        echo "✅ Logging security check completed\n";
    }
    
    /**
     * Check rate limiting
     */
    private function checkRateLimiting()
    {
        echo "🚦 Checking Rate Limiting...\n";
        
        // Check if rate limiting tables exist
        try {
            $stmt = $this->db->query("SHOW TABLES LIKE 'rate_limits'");
            if (!$stmt->fetch()) {
                $this->issues[] = "Rate limiting table does not exist";
            }
        } catch (Exception $e) {
            $this->warnings[] = "Could not verify rate limiting configuration";
        }
        
        echo "✅ Rate limiting check completed\n";
    }
    
    /**
     * Check CSRF protection
     */
    private function checkCSRFProtection()
    {
        echo "🛡️ Checking CSRF Protection...\n";
        
        // Check if CSRF tokens are implemented
        $viewFiles = glob(__DIR__ . '/../app/Views/**/*.php');
        $csrfFound = false;
        
        foreach ($viewFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'csrf_token') !== false) {
                $csrfFound = true;
                break;
            }
        }
        
        if (!$csrfFound) {
            $this->warnings[] = "CSRF protection may not be implemented in all forms";
        }
        
        echo "✅ CSRF protection check completed\n";
    }
    
    /**
     * Check SQL injection protection
     */
    private function checkSQLInjectionProtection()
    {
        echo "💉 Checking SQL Injection Protection...\n";
        
        // Check for raw SQL queries without prepared statements
        $files = glob(__DIR__ . '/../app/**/*.php');
        foreach ($files as $file) {
            $content = file_get_contents($file);
            if (preg_match('/query\s*\(\s*[\'"]\s*SELECT.*\$/', $content)) {
                $this->warnings[] = "Potential SQL injection vulnerability in $file";
            }
        }
        
        echo "✅ SQL injection protection check completed\n";
    }
    
    /**
     * Check XSS protection
     */
    private function checkXSSProtection()
    {
        echo "🛡️ Checking XSS Protection...\n";
        
        // Check for unescaped output
        $viewFiles = glob(__DIR__ . '/../app/Views/**/*.php');
        foreach ($viewFiles as $file) {
            $content = file_get_contents($file);
            if (preg_match('/echo\s+\$[^;]*;/', $content) && strpos($content, 'htmlspecialchars') === false) {
                $this->warnings[] = "Potential XSS vulnerability in $file (unescaped output)";
            }
        }
        
        echo "✅ XSS protection check completed\n";
    }
    
    /**
     * Check file upload security
     */
    private function checkFileUploadSecurity()
    {
        echo "📤 Checking File Upload Security...\n";
        
        // Check file upload configuration
        $maxFileSize = ini_get('upload_max_filesize');
        $maxPostSize = ini_get('post_max_size');
        
        if ($maxFileSize > '10M') {
            $this->warnings[] = "File upload size limit is too high ($maxFileSize)";
        }
        
        if ($maxPostSize > '10M') {
            $this->warnings[] = "POST size limit is too high ($maxPostSize)";
        }
        
        echo "✅ File upload security check completed\n";
    }
    
    /**
     * Check API security
     */
    private function checkAPISecurity()
    {
        echo "🔌 Checking API Security...\n";
        
        // Check API endpoints for authentication
        $apiFiles = glob(__DIR__ . '/../app/Controllers/Api/*.php');
        foreach ($apiFiles as $file) {
            $content = file_get_contents($file);
            if (strpos($content, 'requireAuth') === false && strpos($content, 'requirePermission') === false) {
                $this->warnings[] = "API endpoint $file may not have proper authentication";
            }
        }
        
        echo "✅ API security check completed\n";
    }
    
    /**
     * Generate security report
     */
    private function generateReport()
    {
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "🔍 SECURITY AUDIT REPORT\n";
        echo str_repeat("=", 60) . "\n\n";
        
        if (empty($this->issues) && empty($this->warnings)) {
            echo "✅ All security checks passed! Your system appears to be secure.\n\n";
        } else {
            if (!empty($this->issues)) {
                echo "🚨 CRITICAL ISSUES FOUND:\n";
                echo str_repeat("-", 30) . "\n";
                foreach ($this->issues as $issue) {
                    echo "• $issue\n";
                }
                echo "\n";
            }
            
            if (!empty($this->warnings)) {
                echo "⚠️ WARNINGS:\n";
                echo str_repeat("-", 20) . "\n";
                foreach ($this->warnings as $warning) {
                    echo "• $warning\n";
                }
                echo "\n";
            }
        }
        
        // Generate recommendations
        $this->generateRecommendations();
        
        echo "\n" . str_repeat("=", 60) . "\n";
        echo "📊 SUMMARY:\n";
        echo "• Critical Issues: " . count($this->issues) . "\n";
        echo "• Warnings: " . count($this->warnings) . "\n";
        echo "• Recommendations: " . count($this->recommendations) . "\n";
        echo str_repeat("=", 60) . "\n";
    }
    
    /**
     * Generate security recommendations
     */
    private function generateRecommendations()
    {
        echo "💡 SECURITY RECOMMENDATIONS:\n";
        echo str_repeat("-", 35) . "\n";
        
        $this->recommendations = [
            "Enable HTTPS for all communications",
            "Implement regular security updates",
            "Set up automated security monitoring",
            "Conduct regular penetration testing",
            "Implement multi-factor authentication",
            "Set up automated backups",
            "Monitor security logs regularly",
            "Implement intrusion detection system",
            "Regular security training for users",
            "Keep all dependencies updated"
        ];
        
        foreach ($this->recommendations as $recommendation) {
            echo "• $recommendation\n";
        }
    }
}

// Run the security audit
if (php_sapi_name() === 'cli') {
    $audit = new SecurityAudit();
    $audit->run();
} else {
    echo "This script should be run from the command line.\n";
    exit(1);
} 