# Deployment Guide

## 🚀 Production Deployment Checklist

### 1. Server Requirements
- **PHP**: 7.4+ or 8.0+ with required extensions:
  - PDO MySQL
  - mbstring
  - openssl
  - json
  - curl
  - gd (for image processing)
- **MySQL**: 5.7+ or MariaDB 10.3+
- **Web Server**: Apache 2.4+ or Nginx 1.16+
- **Composer**: Latest version
- **SSL Certificate**: Required for production

### 2. Pre-deployment Steps

#### 2.1 Environment Setup
```bash
# Clone repository
git clone [repository-url] /var/www/labor

# Set permissions
sudo chown -R www-data:www-data /var/www/labor
sudo chmod -R 755 /var/www/labor
sudo chmod -R 775 /var/www/labor/storage
sudo chmod -R 775 /var/www/labor/public/uploads

# Install dependencies
cd /var/www/labor
composer install --optimize-autoloader --no-dev
```

#### 2.2 Environment Configuration
```bash
# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

Required environment variables:
```env
APP_NAME="Laboratory Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_KEY=your-32-character-secret-key

DB_HOST=localhost
DB_DATABASE=labor_production
DB_USERNAME=labor_user
DB_PASSWORD=secure_password

SESSION_SECURE_COOKIE=true
```

#### 2.3 Database Setup
```bash
# Create database
mysql -u root -p
CREATE DATABASE labor_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'labor_user'@'localhost' IDENTIFIED BY 'secure_password';
GRANT ALL PRIVILEGES ON labor_production.* TO 'labor_user'@'localhost';
FLUSH PRIVILEGES;

# Run migrations
php database/migrate.php
```

### 3. Web Server Configuration

#### 3.1 Apache Configuration
```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot /var/www/labor/public
    
    <Directory /var/www/labor/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Redirect to HTTPS
    RewriteEngine on
    RewriteCond %{SERVER_NAME} =yourdomain.com
    RewriteRule ^ https://%{SERVER_NAME}%{REQUEST_URI} [END,NE,R=permanent]
</VirtualHost>

<VirtualHost *:443>
    ServerName yourdomain.com
    DocumentRoot /var/www/labor/public
    
    SSLEngine on
    SSLCertificateFile /path/to/certificate.crt
    SSLCertificateKeyFile /path/to/private.key
    
    <Directory /var/www/labor/public>
        AllowOverride All
        Require all granted
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options SAMEORIGIN
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
</VirtualHost>
```

#### 3.2 Nginx Configuration
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name yourdomain.com;
    root /var/www/labor/public;
    index index.php;
    
    ssl_certificate /path/to/certificate.crt;
    ssl_certificate_key /path/to/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512;
    ssl_prefer_server_ciphers off;
    
    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options SAMEORIGIN;
    add_header X-XSS-Protection "1; mode=block";
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains";
    
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
    
    location ~ /\. {
        deny all;
    }
    
    location ~* \.(jpg|jpeg|png|gif|ico|css|js)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

### 4. Security Hardening

#### 4.1 File Permissions
```bash
# Application files
find /var/www/labor -type f -exec chmod 644 {} \;
find /var/www/labor -type d -exec chmod 755 {} \;

# Executable files
chmod +x /var/www/labor/database/migrate.php

# Writable directories
chmod -R 775 /var/www/labor/storage
chmod -R 775 /var/www/labor/public/uploads

# Sensitive files
chmod 600 /var/www/labor/.env
```

#### 4.2 Hide Sensitive Files
```bash
# Create .htaccess in root
cat > /var/www/labor/.htaccess << 'EOF'
<Files ".env">
    Order allow,deny
    Deny from all
</Files>
<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>
<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>
EOF
```

### 5. Performance Optimization

#### 5.1 PHP Configuration
```ini
# /etc/php/8.0/fpm/php.ini
memory_limit = 256M
max_execution_time = 300
max_input_vars = 3000
upload_max_filesize = 10M
post_max_size = 10M

# Enable opcache
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=4000
opcache.validate_timestamps=0
```

#### 5.2 Database Optimization
```sql
-- Optimize tables
OPTIMIZE TABLE patients, exams, exam_results, invoices;

-- Add indexes for better performance
CREATE INDEX idx_patients_created_lab ON patients(lab_id, created_at);
CREATE INDEX idx_results_lab_date ON exam_results(lab_id, result_date);
CREATE INDEX idx_invoices_lab_status_date ON invoices(lab_id, status, created_at);
```

### 6. Monitoring and Maintenance

#### 6.1 Log Rotation
```bash
# /etc/logrotate.d/labor
/var/www/labor/storage/logs/*.log {
    daily
    missingok
    rotate 30
    compress
    delaycompress
    notifempty
    create 644 www-data www-data
    postrotate
        systemctl reload php8.0-fpm
    endscript
}
```

#### 6.2 Backup Script
```bash
#!/bin/bash
# /usr/local/bin/labor-backup.sh

DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/labor"
DB_NAME="labor_production"
DB_USER="labor_user"
DB_PASS="secure_password"

# Create backup directory
mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u$DB_USER -p$DB_PASS $DB_NAME > $BACKUP_DIR/database_$DATE.sql

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/labor/public/uploads /var/www/labor/storage

# Clean old backups (keep 30 days)
find $BACKUP_DIR -type f -mtime +30 -delete
```

#### 6.3 Cron Jobs
```bash
# Add to crontab
crontab -e

# Daily backup at 2 AM
0 2 * * * /usr/local/bin/labor-backup.sh

# Clean cache every hour
0 * * * * php /var/www/labor/cache-clean.php

# Send daily reports at 8 AM
0 8 * * * php /var/www/labor/daily-report.php
```

### 7. SSL Certificate Setup (Let's Encrypt)

```bash
# Install certbot
sudo apt update
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal
sudo crontab -e
0 12 * * * /usr/bin/certbot renew --quiet
```

### 8. Firewall Configuration

```bash
# UFW setup
sudo ufw allow ssh
sudo ufw allow 'Apache Full'
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306/tcp  # MySQL (restrict to localhost in production)
sudo ufw enable
```

### 9. Health Checks

Create monitoring endpoint:
```bash
# /var/www/labor/public/health
echo "OK - $(date)" > /var/www/labor/public/health.txt
```

### 10. Final Verification

1. **Test all login flows**
2. **Verify database connections**
3. **Check file upload functionality**
4. **Test API endpoints**
5. **Verify SSL certificate**
6. **Check log file permissions**
7. **Test backup procedures**
8. **Verify email functionality**

### 11. Go Live Steps

1. **Update DNS records**
2. **Enable SSL redirect**
3. **Set APP_ENV=production**
4. **Set APP_DEBUG=false**
5. **Clear all caches**
6. **Test all functionality**
7. **Monitor error logs**

## 🔧 Troubleshooting

### Common Issues

1. **Permission Denied**
   ```bash
   sudo chown -R www-data:www-data /var/www/labor
   sudo chmod -R 775 /var/www/labor/storage
   ```

2. **Database Connection Failed**
   - Check database credentials in `.env`
   - Verify MySQL service is running
   - Test connection manually

3. **500 Internal Server Error**
   - Check error logs: `tail -f /var/www/labor/storage/logs/labor.log`
   - Verify file permissions
   - Check PHP error logs

4. **Session Issues**
   - Verify session storage permissions
   - Check session configuration in PHP

## 📞 Support

For deployment assistance, contact the development team with:
- Server specifications
- Error logs
- Configuration files (without sensitive data)

---

**Remember**: Always test in a staging environment before deploying to production!