<?php
/**
 * Security Configuration
 * Centralized security settings for the application
 */

return [
    // Password settings
    'password' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special_chars' => true,
        'max_age_days' => 90, // Password expiration
    ],
    
    // Session settings
    'session' => [
        'lifetime' => 3600, // 1 hour
        'regenerate_interval' => 1800, // 30 minutes
        'secure_cookies' => true,
        'http_only' => true,
        'same_site' => 'Strict',
    ],
    
    // Rate limiting
    'rate_limiting' => [
        'login_attempts' => [
            'max_attempts' => 5,
            'time_window' => 300, // 5 minutes
            'lockout_duration' => 900, // 15 minutes
        ],
        'api_requests' => [
            'max_attempts' => 100,
            'time_window' => 3600, // 1 hour
        ],
        'general_requests' => [
            'max_attempts' => 1000,
            'time_window' => 3600, // 1 hour
        ],
    ],
    
    // CSRF protection
    'csrf' => [
        'token_length' => 32,
        'token_lifetime' => 3600, // 1 hour
    ],
    
    // Input validation
    'validation' => [
        'max_string_length' => 1000,
        'max_file_size' => 10485760, // 10MB
        'allowed_file_types' => [
            'image' => ['jpg', 'jpeg', 'png', 'gif'],
            'document' => ['pdf', 'doc', 'docx', 'xls', 'xlsx'],
            'archive' => ['zip', 'rar'],
        ],
    ],
    
    // Security headers
    'headers' => [
        'X-Frame-Options' => 'DENY',
        'X-Content-Type-Options' => 'nosniff',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Content-Security-Policy' => "default-src 'self'; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' https:;",
    ],
    
    // Logging settings
    'logging' => [
        'security_events' => true,
        'user_activity' => true,
        'database_operations' => true,
        'error_logging' => true,
        'retention_days' => [
            'security_log' => 30,
            'activity_log' => 90,
            'error_log' => 30,
            'audit_log' => 365,
        ],
    ],
    
    // Authentication settings
    'authentication' => [
        'lockout_threshold' => 5,
        'lockout_duration' => 900, // 15 minutes
        'password_history_count' => 5,
        'require_password_change_on_first_login' => true,
        'session_timeout_warning' => 300, // 5 minutes before timeout
    ],
    
    // API security
    'api' => [
        'token_lifetime' => 86400, // 24 hours
        'max_tokens_per_user' => 10,
        'require_https' => true,
        'rate_limit_by_ip' => true,
    ],
    
    // File upload security
    'uploads' => [
        'max_file_size' => 10485760, // 10MB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx'],
        'scan_for_viruses' => false, // Enable if antivirus is available
        'store_outside_webroot' => true,
        'generate_unique_names' => true,
    ],
    
    // Database security
    'database' => [
        'connection_timeout' => 30,
        'max_connections' => 100,
        'query_timeout' => 30,
        'log_slow_queries' => true,
        'slow_query_threshold' => 5, // seconds
    ],
    
    // Error handling
    'error_handling' => [
        'display_errors' => false,
        'log_errors' => true,
        'error_reporting' => E_ALL & ~E_NOTICE & ~E_DEPRECATED,
        'custom_error_pages' => true,
    ],
    
    // Monitoring settings
    'monitoring' => [
        'suspicious_activity_detection' => true,
        'failed_login_threshold' => 10,
        'unusual_ip_threshold' => 5,
        'session_hijacking_detection' => true,
        'sql_injection_detection' => true,
        'xss_detection' => true,
    ],
    
    // Backup and recovery
    'backup' => [
        'auto_backup' => true,
        'backup_frequency' => 'daily',
        'retention_period' => 30, // days
        'encrypt_backups' => true,
        'test_restore' => true,
    ],
    
    // Maintenance
    'maintenance' => [
        'cleanup_old_logs' => true,
        'cleanup_frequency' => 'daily',
        'optimize_database' => true,
        'optimize_frequency' => 'weekly',
        'update_security_logs' => true,
    ],
]; 