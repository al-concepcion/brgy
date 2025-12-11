# Barangay System Maintenance Guide

## Daily Checks
1. Visit `health-check.php` to verify system health
2. Check `logs/php_errors.log` for any errors
3. Verify database connection is working

## Common Issues and Solutions

### Issue: Database Connection Failed
**Symptoms:** Error message "Database Connection Error" or login page not loading

**Solutions:**
1. Check if XAMPP MySQL service is running
2. Open XAMPP Control Panel and start MySQL
3. Verify database credentials in `includes/config.php`:
   - DB_HOST: localhost
   - DB_USER: root
   - DB_PASS: (empty)
   - DB_NAME: barangay_db

4. Run `health-check.php` to diagnose issues

### Issue: Cannot Submit Forms
**Symptoms:** Forms show error or don't submit

**Solutions:**
1. Check if database connection is active
2. Run `health-check.php` to check database tables
3. Verify upload directories exist and are writable
4. Check `logs/php_errors.log` for specific errors

### Issue: Login Page Won't Appear
**Symptoms:** Blank page or error on login.php

**Solutions:**
1. Check if session is working (clear browser cookies)
2. Verify database connection
3. Check if admin_users table exists
4. Run `health-check.php`

### Issue: Photos Not Uploading
**Symptoms:** ID application submitted but photo missing

**Solutions:**
1. Check upload directory permissions (should be writable)
2. Verify upload directories exist:
   - uploads/id_applications/
   - uploads/certifications/
3. Check file size (max 10MB)
4. Verify file type (JPG, PNG, PDF only)

## Preventive Maintenance

### Weekly Tasks
- Run `health-check.php` to verify system health
- Check error logs in `logs/` directory
- Backup database using phpMyAdmin
- Clear old session files

### Monthly Tasks
- Review and archive old contact messages
- Check disk space for uploads folder
- Update admin passwords
- Review user accounts for inactive users

## Backup Procedure
1. Open phpMyAdmin (http://localhost/phpmyadmin)
2. Select `barangay_db` database
3. Click "Export" tab
4. Choose "Quick" export method
5. Click "Go" to download SQL file
6. Save with date: `barangay_db_YYYY-MM-DD.sql`

## Restore Procedure
1. Open phpMyAdmin
2. Select `barangay_db` database (or create new)
3. Click "Import" tab
4. Choose SQL file
5. Click "Go"

## Emergency Contacts
- Database: Check XAMPP MySQL logs in `xampp/mysql/data/`
- PHP Errors: Check `logs/php_errors.log`
- Upload Issues: Check folder permissions

## File Locations
- Configuration: `includes/config.php`
- Error Handler: `includes/error_handler.php`
- Database Schema: `database.sql`
- Health Check: `health-check.php`
- Error Logs: `logs/php_errors.log`
- Upload Files: `uploads/`

## Security Notes
1. Change default admin password (admin/Admin@123)
2. Enable HTTPS in production
3. Set `display_errors = 0` in production
4. Regular database backups
5. Keep upload folder outside web root in production

## Performance Tips
1. Regularly clear old session files
2. Optimize database tables monthly
3. Archive old applications (older than 1 year)
4. Monitor upload folder size
5. Check MySQL slow query log

## Troubleshooting Commands

### Check MySQL Status
```
net start | findstr MySQL
```

### Restart MySQL (as Administrator)
```
net stop MySQL
net start MySQL
```

### Check PHP Version
```
php -v
```

### Test Database Connection
Visit: `http://localhost/webs/health-check.php`
