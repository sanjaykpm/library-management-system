# Library Management System - Complete Project Documentation

## 1. Introduction
The Library Management System is a comprehensive web-based application built with object-oriented PHP following the MVC (Model-View-Controller) architecture. It offers a structured approach to managing a library's book catalog, user memberships, borrowing and returning workflows, fine collection, and real-time system notifications.

## 2. System Architecture
The application runs on a custom PHP MVC micro-framework.

*   **Entry Point (`index.php`)**: Acts as the frontend controller, loading core helper files, CSRF/session configurations, and spl_autoload_register for class loading, then initializing the `App` instance.
*   **Routing System (`core/App.php`)**: Parses the requested URL in the format `/controller/method/params`. It automatically instantiates the matching Controller class from the `controllers/` directory and invokes the targeted method, passing any extra route segments as array parameters.
*   **Controllers (`controllers/`)**: Manages the application's business logic. Examples include `AuthController` for login/registration, `BookController` for catalog management, and `IssueController` for borrowing workflows.
*   **Models (`models/`)**: Manages data access and state corresponding to database tables (e.g., `Book`, `User`, `Fine`, `Notification`). 
*   **Views (`views/`)**: Provides the HTML structure and UI. The application uses a modular view structure divided roughly by access level: `admin/`, `user/`, `auth/`, and `layouts/` for shared components like headers and footers.
*   **Configuration (`config/`)**:
    *   `config.php`: Stores global settings including `URLROOT` and `APP_NAME`.
    *   `constants.php`: Stores fixed values (e.g., `ROLE_ADMIN = 1`, `ROLE_USER = 2`).
    *   `database.php`: Contains the PDO wrapper or static config logic for DB connection.

## 3. Database Schema Overview (`library_db`)
The system consists of 11 relational tables built on InnoDB:

1.  **users**: Stores system credentials for both admins and students (`id`, `student_id`, `reg_no`, `class`, `name`, `email`, `password`, `role_id`).
2.  **roles**: Defines the system access levels (`1`: Admin, `2`: User).
3.  **categories**: Classifications for the book catalog (`id`, `name`).
4.  **authors**: Directory of book authors (`id`, `name`, `bio`).
5.  **books**: Central catalog (`id`, `accession_no`, `title`, `author_id`, `category_id`, `isbn`, `quantity`, `available_quantity`).
6.  **issues**: Active and historical records of borrowed books (`id`, `user_id`, `book_id`, `issue_date`, `return_date`, `actual_return_date`, `status`).
7.  **issue_requests**: Queue for users requesting a specific book. Subject to Admin approval.
8.  **return_requests**: Queue for users submitting books for return processing.
9.  **fines**: Financial logs for overdue issues (`id`, `user_id`, `issue_id`, `amount`, `status`).
10. **notifications**: Alerts triggered on request approvals, rejections, or fines (`id`, `user_id`, `message`, `type`, `status`).
11. **activity_logs**: System audit trail mapping actions taken by users or admins.

## 4. Key Features & Modules

### Admin Module
*   **Dashboard Analytics**: High-level overview of total books, registered users, pending issue requests, and return requests.
*   **Book Inventory**: Add, update, delete books. Track total `quantity` vs `available_quantity`.
*   **Author & Category Classification**: Standardize entry points before books are added to the DB.
*   **User Management**: View user details, manually enroll members, audit user activities.
*   **Issue/Return Workflows**: Approve or decline pending `issue_requests`. Process `return_requests` and assess overdue penalties via the `fines` table.
*   **System Audit Logging**: Track state-changing actions via the `activity_logs` interface.

### User Module
*   **Secure Authentication**: Self-registration, login, and session persistence.
*   **Browsing & Search**: Search the catalog and view book details and current availability.
*   **Requesting Books**: Users can place an `issue_request` on an available book, shifting availability status until admin review.
*   **My Borrowed Books**: Track active `issues`, due dates, and initialize a `return_request`.
*   **Fine Tracking**: Review personal overdue fines and their payment statuses.
*   **Real-time Notifications**: Receive updates when an admin approves an issue/return or issues a penalty.

## 5. File & Directory Structure
```
library-management-system/
│
├── assets/          # Static frontend resources (CSS, JS, Images)
├── config/          # Environment bindings (DB credentials, App constants)
├── controllers/     # App logic (Admin, Auth, Book, User, Fine, etc.)
├── core/            # Framework essentials (App router, Base Controller)
├── database/        # Contains dump library_db.sql
├── helpers/         # Utility functions (CSRF tokens, redirect, session wrappers)
├── middleware/      # Interstitial logic (e.g. Auth checks)
├── models/          # Database interaction (PDO implementations)
├── views/           # PHP UI Templates (Admin portals, Auth layouts, User dashboards)
└── index.php        # Root bootstrap
```

## 6. Security Posture
*   **Password Storage**: Managed uniformly utilizing PHP native `password_hash()` (bcrypt/argon2i) and `password_verify()`.
*   **CSRF Protection**: Critical mutation actions (forms, deletes, post data) are guarded using tokens generated via `helpers/csrf_helper.php`.
*   **Input Sanitization**: Variables undergo strict checks, usually passed directly into PDO prepared statements to avoid SQL injection, with rendering sanitized via `htmlspecialchars`.
*   **Access Control Check**: Deep role-based authorization ensuring standard users cannot access routes destined for `AdminController`.

## 7. Setup & Installation
1.  **Environment Setup**: Utilize a local server like XAMPP or WAMP (Requires PHP 7.4+ and MySQL/MariaDB).
2.  **Database Migration**:
    *   Navigate to phpMyAdmin (`http://localhost/phpmyadmin`).
    *   Create a blank database called `library_db`.
    *   Import the supplied SQL dump: `database/library_db.sql`.
3.  **Application Config**:
    *   Open `config/config.php` and verify `URLROOT` matches your setup (e.g., `http://localhost/library-management-system`).
    *   Open `config/database.php` and map your DB credentials (typically host `localhost`, user `root`, no password).
4.  **Running the System**:
    *   Navigate to your local address mapping matching the config (e.g., `http://localhost/library-management-system`).
    *   **Admin default demo**: Check `users` table or register a new user, update `role_id` to `1` via DB for immediate Admin access.
