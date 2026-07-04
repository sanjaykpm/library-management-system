# Library Management System

A complete PHP-based Library Management System built with a custom MVC framework.

## Features
- **Role-based Authentication**: Admin and User roles.
- **Admin Dashboard**: Manage books, categories, and track issued books.
- **User Dashboard**: Browse available books.
- **Book Issue System**: Track which books are issued to which user.
- **Custom MVC Architecture**: Clean code structure following the requested directory pattern.

## Setup Instructions

### 1. Database Configuration
1.  Open your MySQL management tool (e.g., PHPMyAdmin).
2.  Import the SQL file located at `database/library_db.sql`.
3.  Ensure your database credentials match those in `config/database.php`.
    - Default Host: `localhost`
    - Default User: `root`
    - Default Pass: `''` (empty)
    - Default DB: `library_db`

### 2. Project URLs
- **Student/Member Login**: `http://localhost/library-management-system/auth/login`
- **Admin Login**: `http://localhost/library-management-system/auth/admin_login`

If you host it on a different path, update the `URLROOT` in `config/config.php` and `RewriteBase` in `.htaccess`.

### 3. Default Admin Credentials
- **Email**: `admin@example.com`
- **Password**: `admin123`

## Directory Structure
- `core/`: Application core (Router, Controller, Model, Auth).
- `controllers/`: Application logic.
- `models/`: Database interactions.
- `views/`: HTML templates.
- `helpers/`: Utility functions.
- `assets/`: CSS and JS files.
