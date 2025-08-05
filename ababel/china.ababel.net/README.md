# China Ababel Accounting System

A modern, secure accounting system for the China office with enhanced security, performance, and maintainability.

## 🚀 Recent Improvements

### Security Enhancements

#### 1. **Enhanced Authentication & Authorization**
- **Secure Password Hashing**: Upgraded to Argon2id (preferred) or bcrypt with high cost factors
- **CSRF Protection**: All forms now include CSRF tokens to prevent cross-site request forgery
- **Rate Limiting**: Implemented rate limiting for login attempts and API endpoints
- **Session Security**: Secure session management with automatic regeneration and proper cleanup
- **Input Validation**: Comprehensive input sanitization and validation
- **Suspicious Activity Detection**: Automated detection of unusual login patterns

#### 2. **Database Security**
- **Environment-based Configuration**: Database credentials moved to environment variables
- **Prepared Statements**: All database queries use prepared statements to prevent SQL injection
- **Connection Pooling**: Improved database connection management
- **Audit Logging**: Complete audit trail for all database operations
- **Error Handling**: Graceful error handling without exposing sensitive information

#### 3. **Security Monitoring**
- **Security Logging**: Comprehensive security event logging
- **Activity Tracking**: User activity monitoring and logging
- **Failed Login Tracking**: Detailed tracking of failed login attempts
- **IP Address Validation**: Proper handling of client IP addresses behind proxies

### Code Quality Improvements

#### 1. **Modern PHP Practices**
- **PSR-4 Autoloading**: Proper namespace structure and autoloading
- **Type Hints**: Added type hints for better code clarity
- **Exception Handling**: Comprehensive exception handling throughout the application
- **Dependency Injection**: Improved dependency management

#### 2. **Architecture Improvements**
- **MVC Pattern**: Clean separation of concerns with Model-View-Controller pattern
- **Service Layer**: Added service classes for business logic
- **Repository Pattern**: Improved data access layer
- **Configuration Management**: Centralized configuration management

#### 3. **Performance Optimizations**
- **Database Indexing**: Added proper indexes for better query performance
- **Connection Pooling**: Efficient database connection management
- **Caching Strategy**: Prepared for future caching implementation
- **Query Optimization**: Optimized database queries for better performance

### Database Structure

#### New Security Tables

```sql
-- Rate limiting
CREATE TABLE rate_limits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Security logging
CREATE TABLE security_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_type VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    user_id INT NULL,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enhanced activity logging
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    description TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    metadata JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Audit trail
CREATE TABLE audit_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(100) NOT NULL,
    table_name VARCHAR(100) NULL,
    record_id INT NULL,
    old_values JSON NULL,
    new_values JSON NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔧 Installation & Setup

### Prerequisites
- PHP 8.3 or higher
- MySQL 8.0 or higher
- Composer

### Environment Configuration

1. **Copy environment file**:
   ```bash
   cp .env.example .env
   ```

2. **Configure environment variables**:
   ```env
   APP_ENV=production
   DB_HOST=localhost
   DB_NAME=china_ababel
   DB_USER=your_db_user
   DB_PASSWORD=your_secure_password
   APP_URL=https://china.ababel.net
   ```

3. **Install dependencies**:
   ```bash
   composer install
   ```

4. **Run database migrations**:
   ```bash
   # Create security tables
   mysql -u your_user -p your_database < migrations/create_security_tables.sql
   ```

5. **Set proper permissions**:
   ```bash
   chmod -R 755 storage/
   chmod -R 755 logs/
   ```

### Security Checklist

- [ ] Change default database credentials
- [ ] Configure HTTPS/SSL certificates
- [ ] Set up proper file permissions
- [ ] Configure firewall rules
- [ ] Enable security headers
- [ ] Set up monitoring and alerting
- [ ] Regular security audits

## 🛡️ Security Features

### Authentication
- **Multi-factor Authentication**: Ready for future MFA implementation
- **Password Policies**: Enforced strong password requirements
- **Account Lockout**: Automatic account lockout after failed attempts
- **Session Management**: Secure session handling with automatic cleanup

### Authorization
- **Role-based Access Control**: Granular permission system
- **Permission Hierarchy**: Admin > Accountant > Viewer
- **API Token Management**: Secure API access with token-based authentication

### Data Protection
- **Input Sanitization**: All user inputs are sanitized
- **Output Encoding**: Proper HTML encoding to prevent XSS
- **SQL Injection Prevention**: All queries use prepared statements
- **CSRF Protection**: All forms protected against CSRF attacks

### Monitoring & Logging
- **Security Events**: Comprehensive security event logging
- **User Activity**: Detailed user activity tracking
- **Audit Trail**: Complete audit trail for sensitive operations
- **Error Logging**: Secure error logging without exposing sensitive data

## 📁 Project Structure

```
china.ababel.net/
├── app/
│   ├── Controllers/          # Application controllers
│   ├── Core/                # Core framework classes
│   ├── Models/              # Data models
│   ├── Services/            # Business logic services
│   └── Views/               # View templates
├── config/                  # Configuration files
├── lang/                    # Language files
├── migrations/              # Database migrations
├── public/                  # Public web root
├── storage/                 # File storage
│   ├── logs/               # Application logs
│   ├── cache/              # Cache files
│   └── exports/            # Export files
└── vendor/                 # Composer dependencies
```

## 🔍 Code Quality Standards

### Coding Standards
- **PSR-12**: PHP coding standards compliance
- **Type Hints**: Comprehensive type hinting
- **Documentation**: PHPDoc comments for all methods
- **Error Handling**: Proper exception handling
- **Security First**: Security considerations in all code

### Performance Standards
- **Database Optimization**: Efficient queries with proper indexing
- **Memory Management**: Proper resource cleanup
- **Caching Ready**: Prepared for caching implementation
- **Connection Pooling**: Efficient database connections

### Security Standards
- **OWASP Compliance**: Following OWASP security guidelines
- **Input Validation**: Comprehensive input validation
- **Output Encoding**: Proper output encoding
- **Access Control**: Proper authorization checks

## 🚨 Security Best Practices

### For Developers
1. **Never commit sensitive data** to version control
2. **Always validate and sanitize** user inputs
3. **Use prepared statements** for all database queries
4. **Implement proper error handling** without exposing sensitive information
5. **Follow the principle of least privilege** for user permissions
6. **Regular security audits** of the codebase
7. **Keep dependencies updated** to patch security vulnerabilities

### For Administrators
1. **Regular backups** of database and files
2. **Monitor security logs** for suspicious activity
3. **Update system packages** regularly
4. **Configure firewall rules** appropriately
5. **Use HTTPS** for all communications
6. **Implement rate limiting** at the server level
7. **Regular security assessments**

## 📊 Monitoring & Maintenance

### Log Files
- `storage/logs/application.log` - Application logs
- `storage/logs/security.log` - Security events
- `storage/logs/database.log` - Database operations
- `storage/logs/model.log` - Model operations

### Regular Maintenance Tasks
1. **Clean old logs** (older than 90 days)
2. **Clean expired sessions** (older than 24 hours)
3. **Clean rate limit entries** (older than 1 hour)
4. **Update security logs** (older than 30 days)
5. **Database optimization** and index maintenance

## 🔧 Configuration

### Environment Variables
```env
# Application
APP_ENV=production
APP_URL=https://china.ababel.net
APP_TIMEZONE=Asia/Shanghai
APP_LOCALE=ar

# Database
DB_HOST=localhost
DB_NAME=china_ababel
DB_USER=your_user
DB_PASSWORD=your_password

# Security
SESSION_SECRET=your_session_secret
CSRF_SECRET=your_csrf_secret

# API Keys
PORT_SUDAN_API_URL=https://ababel.net/app/api/china_sync.php
PORT_SUDAN_API_KEY=your_api_key
WEBHOOK_API_KEY=your_webhook_key
```

## 🆘 Troubleshooting

### Common Issues

1. **Database Connection Issues**
   - Check database credentials in `.env`
   - Verify database server is running
   - Check network connectivity

2. **Permission Issues**
   - Ensure proper file permissions (755 for directories, 644 for files)
   - Check web server user permissions

3. **Security Issues**
   - Review security logs for suspicious activity
   - Check rate limiting configuration
   - Verify CSRF token implementation

### Support
For technical support or security issues, please contact the development team.

## 📝 Changelog

### Version 2.0.0 (Current)
- **Major Security Overhaul**: Complete security enhancement
- **Modern PHP Architecture**: Updated to modern PHP practices
- **Enhanced Database Layer**: Improved database operations
- **Comprehensive Logging**: Added detailed logging system
- **Performance Optimizations**: Database and code optimizations

### Version 1.x (Previous)
- Basic functionality
- Simple authentication
- Basic database operations

## 📄 License

This project is proprietary software. All rights reserved.

---

**⚠️ Security Notice**: This system handles sensitive financial data. Please ensure all security measures are properly implemented and regularly maintained. 