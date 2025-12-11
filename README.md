# Barangay Santo Niño E-Services Portal

A fully functional PHP-based web application for Barangay ID management, certification requests, and application tracking with user authentication system.

## Features

### User Authentication
- **User Registration**: Create an account with email verification
- **Login System**: Secure login with password hashing (bcrypt)
- **User Profile**: View and edit personal information
- **My Applications**: Track all submitted applications in one place
- **Session Management**: Secure session handling and logout

### Public Features
- **Homepage**: Dynamic content with announcements and statistics
- **Apply for Barangay ID**: Multi-step form with file upload
- **Request Certifications**: Apply for various barangay certificates
  - Certificate of Residency (₱50.00)
  - Certificate of Indigency (Free)
  - Barangay Clearance (₱100.00)
  - Barangay Business Clearance (₱200.00)
  - Certificate of Good Moral Character (₱50.00)
- **Track Application**: Real-time status tracking with timeline
- **Contact Form**: Submit inquiries and messages
- **About Page**: Barangay officials and contact information

### Admin Features
- **Dashboard**: Overview of all applications and statistics
- **Application Management**: View and update ID applications
- **Certification Management**: Manage certification requests
- **Message Management**: View contact form submissions
- **Status Updates**: Change application status with history tracking

### Special Features
- **Beautiful Gradient Navbar**: Modern purple gradient design with icons
- **Responsive Design**: Works perfectly on all devices
- **Smooth Animations**: Scroll effects and transitions
- **User Dropdown Menu**: Quick access to profile and applications
- **Protected Routes**: Login required for certain pages

## Technology Stack

- **Frontend**: Bootstrap 5, Font Awesome, JavaScript
- **Backend**: PHP 7.4+ with PDO
- **Database**: MySQL/MariaDB
- **Server**: Apache (XAMPP)

## Installation Instructions

### Prerequisites
- XAMPP (or similar Apache + MySQL + PHP environment)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Setup Steps

1. **Start XAMPP Services**
   - Start Apache
   - Start MySQL

2. **Database Setup**
   - The database will be automatically created when you first access the website
   - Database name: `barangay_db`
   - Default credentials are in `includes/config.php`

3. **Access the Website**
   - Open your browser and navigate to: `http://localhost/webs/`
   - The database will auto-initialize on first visit

4. **Admin Access**
   - URL: `http://localhost/webs/admin/`
   - Default credentials:
     - Username: `admin`
     - Password: `admin123`

## File Structure

```
webs/
├── admin/                      # Admin panel
│   ├── index.php              # Admin dashboard
│   ├── login.php              # Admin login
│   └── logout.php             # Admin logout
├── assets/
│   ├── css/
│   │   └── style.css          # Custom styles (with auth & navbar styles)
│   └── js/
│       ├── main.js            # Main JavaScript
│       ├── apply-id.js        # ID application JS
│       └── tracking.js        # Tracking JS
├── includes/
│   ├── config.php             # Database config & functions
│   ├── header.php             # Page header (special navbar)
│   └── footer.php             # Page footer
├── uploads/                   # Uploaded documents (auto-created)
│   ├── id_applications/
│   └── certifications/
├── database.sql               # Database schema (with users table)
├── index.php                  # Homepage
├── login.php                  # User login page
├── register.php               # User registration page
├── logout.php                 # User logout
├── profile.php                # User profile page
├── my-applications.php        # User's applications dashboard
├── apply-id.php              # ID application form
├── request-certification.php  # Certification request
├── track-application.php      # Application tracking
└── about-contact.php          # About & contact page
```

## Database Tables

- **users**: Registered user accounts
- **id_applications**: Barangay ID applications
- **certification_requests**: Certificate requests
- **status_history**: Application status tracking
- **contact_messages**: Contact form submissions
- **admin_users**: Admin user accounts
- **announcements**: Homepage announcements

## Configuration

### Database Settings
Edit `includes/config.php` to change database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'barangay_db');
```

### Upload Settings
- Maximum file size: 10MB
- Allowed formats: JPG, PNG, PDF
- Upload directory: `uploads/`

## Usage

### For Residents

1. **Register an Account**
   - Click "Register" in the navbar
   - Fill in your details
   - Create account and auto-login

2. **Login**
   - Click "Login" in the navbar
   - Enter email and password
   - Access your dashboard

3. **View Profile**
   - Click on your name in navbar
   - Select "My Profile"
   - Edit your information

4. **Apply for Barangay ID**
   - Navigate to "Apply for ID"
   - Complete 3-step form (Personal Info, Contact Details, Documents)
   - Upload required documents
   - Receive reference number

5. **Request Certification**
   - Navigate to "Request Certification"
   - Select certificate type
   - Fill in personal information
   - Choose claim method (pickup/delivery)
   - Submit request

6. **Track Application**
   - Navigate to "Track Application" OR
   - Go to "My Applications" from navbar
   - View all your applications with status
   - Click "View" to see detailed timeline

### For Administrators

1. **Login to Admin Panel**
   - Go to `/admin/`
   - Enter credentials
   - Access dashboard

2. **Manage Applications**
   - View all pending/processed applications
   - Update application status
   - View applicant details
   - Track status history

## Features Implemented

✅ **User Authentication System**
✅ User registration with validation
✅ Secure login with password hashing
✅ User profile management
✅ My applications dashboard
✅ Protected routes
✅ Session management
✅ **Special Gradient Navbar**
✅ Beautiful purple gradient design
✅ Smooth scroll effects
✅ User dropdown menu
✅ Responsive mobile design
✅ Multi-step form with session management
✅ File upload with validation
✅ Database integration with PDO
✅ Application tracking system
✅ Status history logging
✅ Admin authentication
✅ Responsive design
✅ Form validation (client & server-side)
✅ Dynamic content loading
✅ Error handling

## Security Features

- SQL injection prevention (PDO prepared statements)
- XSS protection (htmlspecialchars)
- File upload validation
- Session management
- Password hashing (bcrypt)
- Input sanitization

## Sample Test Data

The database includes sample data:
- **ID Application**: Reference `BID-12345678`
- **Certification**: Reference `CERT-87654321`
- **Admin User**: username `admin`, password `admin123`

## Support

For issues or questions, use the contact form on the website or visit the About & Contact page.

## License

This project is created for educational purposes for Barangay Santo Niño.

---

**Maka-Diyos, Maka-Tao, Makakalikasan, at Makabansa**
