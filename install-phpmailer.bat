@echo off
echo Installing PHPMailer...
echo.

REM Check if composer is installed
where composer >nul 2>nul
if %ERRORLEVEL% NEQ 0 (
    echo Composer not found. Installing Composer first...
    echo.
    
    REM Download Composer installer
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    
    REM Install Composer
    php composer-setup.php --install-dir=%CD% --filename=composer.phar
    
    REM Clean up
    php -r "unlink('composer-setup.php');"
    
    echo.
    echo Composer installed successfully!
    echo.
)

REM Install PHPMailer using composer
if exist composer.phar (
    php composer.phar install
) else (
    composer install
)

echo.
echo ========================================
echo PHPMailer Installation Complete!
echo ========================================
echo.
echo IMPORTANT: Configure your email settings in includes/email.php
echo.
echo For Gmail users:
echo 1. Go to your Google Account settings
echo 2. Enable 2-Step Verification
echo 3. Generate an App Password
echo 4. Use the App Password in includes/email.php
echo.
echo Update these constants in includes/email.php:
echo - SMTP_USERNAME (your email)
echo - SMTP_PASSWORD (your app password)
echo - FROM_EMAIL (your email)
echo.
pause
