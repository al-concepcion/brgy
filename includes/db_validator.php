<?php
/**
 * Database Connection Validator
 * Include this at the top of critical pages to ensure database is available
 */

function validate_database_connection() {
    global $conn;
    
    if (!isset($conn) || $conn === null) {
        error_log('Database connection is null');
        return false;
    }
    
    try {
        $conn->query("SELECT 1");
        return true;
    } catch (PDOException $e) {
        error_log('Database connection test failed: ' . $e->getMessage());
        return false;
    }
}

function ensure_database_or_fail() {
    if (!validate_database_connection()) {
        die('
            <div style="max-width: 600px; margin: 100px auto; padding: 30px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 8px; font-family: Arial, sans-serif;">
                <h2 style="color: #856404; margin-top: 0;">⚠️ Service Temporarily Unavailable</h2>
                <p style="color: #856404; line-height: 1.6;">
                    We are experiencing technical difficulties with our database connection. 
                    Please try again in a few moments.
                </p>
                <p style="color: #856404; line-height: 1.6;">
                    If the problem persists, please contact the administrator.
                </p>
                <a href="javascript:window.location.reload()" style="display: inline-block; margin-top: 15px; padding: 10px 20px; background: #ffc107; color: #000; text-decoration: none; border-radius: 4px;">
                    Try Again
                </a>
            </div>
        ');
    }
}
?>
