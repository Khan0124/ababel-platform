# Laboratory Management System 🧪

A comprehensive, professional-grade laboratory management system built with modern PHP architecture, designed for medical laboratories and diagnostic centers.

## 🌟 Features

### Core Functionality
- **Patient Management**: Complete patient records with medical history
- **Exam Management**: Comprehensive test catalog with results tracking
- **Employee Management**: Staff management with role-based permissions
- **Inventory Management**: Stock tracking and supply management
- **Financial Management**: Invoicing, payments, and revenue tracking
- **Reporting System**: Detailed analytics and customizable reports

### Technical Features
- **Modern PHP Architecture**: Clean MVC structure with dependency injection
- **Security First**: CSRF protection, rate limiting, input validation
- **Responsive Design**: Mobile-friendly interface with RTL support
- **Performance Optimized**: Caching, lazy loading, and optimized queries
- **Professional UI/UX**: Modern design with Arabic language support
- **Comprehensive Logging**: Activity tracking and error monitoring

## 🚀 Quick Start

### Prerequisites
- PHP 7.4+ or 8.0+
- MySQL 5.7+ or MariaDB 10.3+
- Composer
- Web server (Apache/Nginx)

### Installation

1. **Clone the repository**
   ```bash
   git clone [repository-url]
   cd labor
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. **Database setup**
   ```bash
   # Create your database
   mysql -u root -p -e "CREATE DATABASE labor_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
   
   # Update .env with database credentials
   # Run migrations (when available)
   ```

5. **Set permissions**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/uploads/
   ```

6. **Web server configuration**
   - Point document root to `/public` directory
   - Ensure `.htaccess` rules are enabled

## 📁 Project Structure

```
labor/
├── app/                    # Application code
│   ├── Core/              # Core framework classes
│   ├── Models/            # Data models
│   ├── Controllers/       # Request handlers
│   ├── Services/          # Business logic
│   ├── Middleware/        # Request middleware
│   ├── Helpers/           # Utility functions
│   └── Exceptions/        # Custom exceptions
├── bootstrap/             # Application bootstrap
├── config/                # Configuration files
├── database/              # Database migrations & seeders
├── public/                # Public web files
├── resources/             # Views and assets
├── storage/               # Logs, cache, sessions
├── tests/                 # Test files
├── vendor/                # Composer dependencies
├── admin/                 # Admin panel (legacy)
├── lab/                   # Lab interface (legacy)
├── includes/              # Shared includes
└── assets/                # Static assets
```

## 🔧 Configuration

### Environment Variables

Key environment variables to configure:

```env
# Application
APP_NAME="Laboratory Management System"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_HOST=localhost
DB_DATABASE=labor_db
DB_USERNAME=your_username
DB_PASSWORD=your_password

# Security
APP_KEY=your-secret-key-here
SESSION_LIFETIME=120

# Limits
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT_MINUTES=15
MAX_UPLOAD_SIZE=10M
```

### Web Server Configuration

#### Apache
Ensure mod_rewrite is enabled and use the provided `.htaccess` file.

#### Nginx
```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /path/to/labor/public;
    index index.php;

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
}
```

## 👥 User Roles

### System Administrator
- Manage laboratory accounts
- Monitor system performance
- Configure global settings
- View system-wide reports

### Laboratory Manager
- Manage employees and permissions
- Configure lab-specific settings
- Access financial reports
- Manage inventory

### Laboratory Technician
- Process patient samples
- Enter test results
- Manage patient records
- Generate reports

### Receptionist
- Register new patients
- Schedule appointments
- Handle payments
- Print invoices

## 🔒 Security Features

- **Authentication**: Secure login with rate limiting
- **Authorization**: Role-based access control
- **CSRF Protection**: All forms protected against CSRF attacks
- **Input Validation**: Comprehensive server-side validation
- **SQL Injection Prevention**: Prepared statements throughout
- **XSS Protection**: Output escaping and CSP headers
- **Session Security**: Secure session management
- **File Upload Security**: Restricted file types and locations

## 📊 API Documentation

### Authentication
```php
POST /api/v1/auth/login
{
    "email": "user@example.com",
    "password": "password"
}
```

### Patients
```php
GET /api/v1/patients          # List all patients
POST /api/v1/patients         # Create patient
GET /api/v1/patients/{id}     # Get patient details
PUT /api/v1/patients/{id}     # Update patient
DELETE /api/v1/patients/{id}  # Delete patient
```

### Exams
```php
GET /api/v1/exams             # List all exams
POST /api/v1/exams            # Create exam
GET /api/v1/exams/{id}        # Get exam details
PUT /api/v1/exams/{id}        # Update exam
```

## 🧪 Testing

### Running Tests
```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test suite
vendor/bin/phpunit tests/Unit/
vendor/bin/phpunit tests/Feature/
```

### Test Structure
- **Unit Tests**: Test individual classes and methods
- **Feature Tests**: Test complete workflows
- **Integration Tests**: Test database interactions

## 🚀 Performance Optimization

### Caching
- **Query Caching**: Database query results cached
- **Session Caching**: Redis/Memcached support
- **File Caching**: Static file caching with proper headers

### Database Optimization
- **Indexes**: Proper indexing on frequently queried columns
- **Query Optimization**: Optimized queries with EXPLAIN analysis
- **Connection Pooling**: Efficient database connection management

### Frontend Optimization
- **Asset Minification**: CSS/JS minification in production
- **Image Optimization**: Optimized images with proper formats
- **CDN Ready**: Asset URLs configurable for CDN usage

## 📝 Logging

The system provides comprehensive logging:

### Log Levels
- **DEBUG**: Development information
- **INFO**: General information
- **WARNING**: Warning conditions
- **ERROR**: Error conditions
- **CRITICAL**: Critical conditions

### Log Files
- `storage/logs/labor.log` - General application log
- `storage/logs/error.log` - Error-specific log
- `storage/logs/security.log` - Security events
- `storage/logs/performance.log` - Performance metrics

## 🔄 Backup & Maintenance

### Database Backup
```bash
# Create backup
mysqldump -u username -p labor_db > backup_$(date +%Y%m%d_%H%M%S).sql

# Restore backup
mysql -u username -p labor_db < backup_file.sql
```

### File Backup
```bash
# Backup uploads and logs
tar -czf files_backup_$(date +%Y%m%d).tar.gz storage/ public/uploads/
```

### Maintenance Mode
```bash
# Enable maintenance mode
touch storage/maintenance

# Disable maintenance mode
rm storage/maintenance
```

## 🐛 Troubleshooting

### Common Issues

1. **Database Connection Error**
   - Check database credentials in `.env`
   - Verify database server is running
   - Check firewall settings

2. **Permission Denied**
   ```bash
   chmod -R 755 storage/
   chmod -R 755 public/uploads/
   chown -R www-data:www-data storage/
   ```

3. **Session Issues**
   - Check session directory permissions
   - Verify session configuration in PHP
   - Clear session files if corrupted

4. **Upload Issues**
   - Check PHP upload settings
   - Verify upload directory permissions
   - Check file size limits

### Debug Mode
Enable debug mode in development:
```env
APP_DEBUG=true
APP_ENV=development
```

## 📄 License

This project is proprietary software. All rights reserved.

## 🤝 Support

For support and questions:
- Check the documentation
- Review common issues
- Contact system administrator

## 🚀 Deployment

### Production Checklist
- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper database credentials
- [ ] Set up SSL certificate
- [ ] Configure web server properly
- [ ] Set up monitoring and logging
- [ ] Configure backup strategy
- [ ] Test all functionality

### Monitoring
- Set up application monitoring
- Configure error reporting
- Monitor performance metrics
- Set up log rotation

---

Built with ❤️ for modern laboratory management.