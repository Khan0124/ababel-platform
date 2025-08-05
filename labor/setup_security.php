<?php
/**
 * Security Setup Script
 * Run this script to apply security updates to your Labor SaaS system
 */

// Prevent web access
if (php_sapi_name() !== 'cli') {
    die("This script must be run from the command line\n");
}

echo "=== Labor SaaS Security Setup ===\n\n";

// Step 1: Check for .env file
echo "Step 1: Checking environment configuration...\n";
if (!file_exists(__DIR__ . '/.env')) {
    echo "❌ .env file not found!\n";
    echo "📝 Creating .env from .env.example...\n";
    
    if (file_exists(__DIR__ . '/.env.example')) {
        copy(__DIR__ . '/.env.example', __DIR__ . '/.env');
        echo "✅ .env file created. Please edit it with your database credentials.\n";
        echo "\nEdit .env file and run this script again.\n";
        exit(1);
    } else {
        echo "❌ .env.example not found! Please create it manually.\n";
        exit(1);
    }
} else {
    echo "✅ .env file found\n";
}

// Step 2: Test database connection
echo "\nStep 2: Testing database connection...\n";
require_once __DIR__ . '/includes/config_secure.php';
echo "✅ Database connected successfully\n";

// Step 3: Create logs directory
echo "\nStep 3: Creating logs directory...\n";
$logs_dir = __DIR__ . '/logs';
if (!file_exists($logs_dir)) {
    if (mkdir($logs_dir, 0755, true)) {
        echo "✅ Logs directory created\n";
        
        // Create .htaccess to protect logs
        file_put_contents($logs_dir . '/.htaccess', "Deny from all\n");
        echo "✅ Logs directory protected\n";
    } else {
        echo "❌ Failed to create logs directory\n";
    }
} else {
    echo "✅ Logs directory already exists\n";
}

// Step 4: Run database migrations
echo "\nStep 4: Running database migrations...\n";
echo "Execute: php migrations/migrate.php\n";
$output = [];
$return_var = 0;
exec('php ' . __DIR__ . '/migrations/migrate.php', $output, $return_var);
foreach ($output as $line) {
    echo $line . "\n";
}

// Step 5: Generate secure keys
echo "\nStep 5: Generating secure keys...\n";
$env_file = __DIR__ . '/.env';
$env_content = file_get_contents($env_file);

// Generate APP_KEY if not set
if (strpos($env_content, 'APP_KEY=base64:generate_a_32_character_random_string_here') !== false) {
    $app_key = base64_encode(random_bytes(32));
    $env_content = str_replace(
        'APP_KEY=base64:generate_a_32_character_random_string_here',
        'APP_KEY=base64:' . $app_key,
        $env_content
    );
    echo "✅ APP_KEY generated\n";
}

// Generate ENCRYPTION_KEY if not set
if (strpos($env_content, 'ENCRYPTION_KEY=base64:generate_another_32_character_random_string') !== false) {
    $encryption_key = base64_encode(random_bytes(32));
    $env_content = str_replace(
        'ENCRYPTION_KEY=base64:generate_another_32_character_random_string',
        'ENCRYPTION_KEY=base64:' . $encryption_key,
        $env_content
    );
    echo "✅ ENCRYPTION_KEY generated\n";
}

file_put_contents($env_file, $env_content);

// Step 6: Update existing passwords to use secure hashing
echo "\nStep 6: Checking for plaintext passwords...\n";

// Check admins
$result = $conn->query("SELECT id, password FROM admins WHERE password NOT LIKE '$2y$%'");
if ($result && $result->num_rows > 0) {
    echo "⚠️  Found {$result->num_rows} admin(s) with insecure passwords\n";
    echo "These passwords need to be reset. Users will need to use password recovery.\n";
    
    // Invalidate insecure passwords
    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE password NOT LIKE '$2y$%'");
    $temp_hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_ARGON2ID);
    $stmt->bind_param("s", $temp_hash);
    $stmt->execute();
    echo "✅ Insecure admin passwords invalidated\n";
} else {
    echo "✅ All admin passwords are secure\n";
}

// Check lab employees
$result = $conn->query("SELECT id, password FROM lab_employees WHERE password NOT LIKE '$2y$%'");
if ($result && $result->num_rows > 0) {
    echo "⚠️  Found {$result->num_rows} employee(s) with insecure passwords\n";
    echo "These passwords need to be reset. Users will need to use password recovery.\n";
    
    // Invalidate insecure passwords
    $stmt = $conn->prepare("UPDATE lab_employees SET password = ? WHERE password NOT LIKE '$2y$%'");
    $temp_hash = password_hash(bin2hex(random_bytes(32)), PASSWORD_ARGON2ID);
    $stmt->bind_param("s", $temp_hash);
    $stmt->execute();
    echo "✅ Insecure employee passwords invalidated\n";
} else {
    echo "✅ All employee passwords are secure\n";
}

// Step 7: Create .htaccess for security
echo "\nStep 7: Creating security .htaccess file...\n";
$htaccess_content = '# Security Headers
<IfModule mod_headers.c>
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-Content-Type-Options "nosniff"
    Header set X-XSS-Protection "1; mode=block"
    Header set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Protect sensitive files
<FilesMatch "^\.env|composer\.(json|lock)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Protect directories
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(includes|migrations|logs)/ - [F,L]
</IfModule>

# Disable directory browsing
Options -Indexes

# Protect against SQL injection
<IfModule mod_rewrite.c>
    RewriteCond %{QUERY_STRING} (\<|%3C).*script.*(\>|%3E) [NC,OR]
    RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
    RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})
    RewriteRule .* - [F]
</IfModule>
';

file_put_contents(__DIR__ . '/.htaccess', $htaccess_content);
echo "✅ Security .htaccess created\n";

// Step 8: Create upgrade guide
echo "\nStep 8: Creating upgrade guide...\n";
$guide = "# Labor SaaS Security Upgrade Guide

## Files to Update

1. **Configuration Files:**
   - Replace `includes/config.php` references with `includes/config_secure.php`
   - Update all files to use the new secure configuration

2. **Authentication Files:**
   - Replace `auth_employee.php` with `auth_employee_secure.php`
   - Update login files to use new SessionManager

3. **Form Security:**
   - Add CSRF tokens to all forms
   - Use validation.php for input validation

4. **SQL Queries:**
   - Replace all direct queries with prepared statements
   - Fix SQL injection vulnerabilities

## Next Steps

1. Update your web server configuration to use HTTPS
2. Configure proper file permissions (755 for directories, 644 for files)
3. Set up regular backups
4. Monitor security logs regularly
5. Implement rate limiting on your web server

## Important Notes

- All users with insecure passwords will need to reset their passwords
- Test thoroughly in a staging environment before deploying to production
- Keep your .env file secure and never commit it to version control
";

file_put_contents(__DIR__ . '/SECURITY_UPGRADE.md', $guide);
echo "✅ Upgrade guide created: SECURITY_UPGRADE.md\n";

echo "\n=== Security Setup Complete! ===\n";
echo "Next steps:\n";
echo "1. Review and update your .env file\n";
echo "2. Run database migrations: php migrations/migrate.php\n";
echo "3. Update your application files to use secure versions\n";
echo "4. Test thoroughly before deploying to production\n";
echo "5. Set up HTTPS on your web server\n";
?>