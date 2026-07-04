# COMPREHENSIVE PROJECT REPORT: LIBRARY MANAGEMENT SYSTEM

---

## 1. ABSTRACT

The Library Management System (LMS) is a digital solution designed to automate the traditional manual processes of a library. Developed using a custom PHP MVC framework, the system provides a robust platform for managing book inventories, member registrations, and circulation activities. It features role-based access control, allowing administrators to manage assets efficiently while providing students with a seamless interface to browse and track their borrowing history. The system focuses on data integrity, user-friendliness, and scalability.

---

## 2. INTRODUCTION

### 2.1 Overview
In any academic or public institution, a library serves as a critical knowledge hub. Managing thousands of books and hundreds of members manually is prone to errors, data loss, and inefficiency. This project aims to digitize these operations through a secure web application.

### 2.2 Objectives
- Automate book issuing and returning processes.
- Maintain accurate records of book availability.
- Provide real-time dashboards for administrators.
- Ensure secure authentication and role management.
- Calculate and track overdue fines automatically.

---

## 3. SYSTEM ANALYSIS

### 3.1 Existing System
The manual system relies on physical registers and card-based tracking. 
**Disadvantages:**
- Time-consuming data entry and retrieval.
- High risk of human error in fine calculation.
- Difficulty in searching for specific books.
- No real-time visibility into book status across the entire library.

### 3.2 Proposed System
The proposed LMS replaces manual registers with a centralized MySQL database and a web interface.
**Advantages:**
- Instant search and retrieval of book information.
- Automated fine management and overdue alerts.
- Data consistency across all modules.
- Secure access for both administrators and members.

---

## 4. SYSTEM SPECIFICATION

### 4.1 Hardware Specification
- **Processor**: Intel Core i3 or higher (Minimum 2.0 GHz)
- **RAM**: 4 GB or higher
- **Storage**: 500 MB free disk space for application and logs.
- **Network**: Local Area Network (LAN) or Internet for remote access.

### 4.2 Software Specification
- **Operating System**: Windows / Linux / macOS
- **Development Stack**: XAMPP (Apache, MySQL, PHP)
- **PHP Version**: 7.4 or higher
- **Security**: 
  - **CSRF Protection**: All forms are protected with anti-CSRF tokens.
  - **Input Sanitization**: All inputs are sanitized using `FILTER_SANITIZE_STRING`.
  - **SQL Injection Prevention**: Uses PDO prepared statements.
  - **Password Hashing**: Uses `password_hash` (Bcrypt).
- **Optimization**:
  - **Database Indexes**: Added indexes on frequently searched columns (Title, ISBN, Email).
  - **Pagination**: Implemented pagination for Book management.
- **Reliability**:
  - **Transactions**: Uses database transactions for critical operations (Issue/Return).
  - **Activity Logging**: Tracks key user actions (Login, Issue, Return).
- **Database**: MySQL 5.7 / MariaDB 10.4
- **Frontend Framework**: Bootstrap 5, FontAwesome (for icons)

---

## 5. SYSTEM DESIGN

### 5.1 MVC Architecture
The system is built on a custom Model-View-Controller design:
- **Model**: Handles database logic and data validation.
- **View**: Manages the UI rendering and user interaction.
- **Controller**: Processes requests, interacts with models, and provides data to views.

### 5.2 System Flow
1. **User Login**: Authenticates via `Auth` controller.
2. **Admin Dashboard**: Fetches statistics via `AdminController`.
3. **Book Management**: Controlled by `BookController`, interacting with `Book` and `Author` models.

---

## 6. DATABASE DICTIONARY

### Table: `users`
| Field | Type | Constraint | Description |
| :--- | :--- | :--- | :--- |
| id | INT | PK, AI | Unique identifier for the user. |
| student_id | VARCHAR | Unique | Registration ID for students. |
| name | VARCHAR | Not NULL | Full name of the user. |
| email | VARCHAR | Unique | Login email address. |
| password | VARCHAR | Not NULL | Hashed password. |
| role_id | INT | FK | References `roles(id)`. |

### Table: `books`
| Field | Type | Constraint | Description |
| :--- | :--- | :--- | :--- |
| id | INT | PK, AI | Unique identifier for the book. |
| accession_no| VARCHAR | Unique | Library-standard accession number. |
| title | VARCHAR | Not NULL | Title of the book. |
| author_id | INT | FK | References `authors(id)`. |
| category_id | INT | FK | References `categories(id)`. |
| quantity | INT | Default 0 | Total copies in stock. |
| created_at | DATETIME | Default NOW() | Record creation time. |
| updated_at | DATETIME | Default NOW() | Record update time. |

### Table: `activity_logs`
| Column | Type | Constraints | Description |
| :--- | :--- | :--- | :--- |
| id | INT | PK, AI | Unique log ID. |
| user_id | INT | FK | User who performed the action. |
| action | VARCHAR | Not NULL | Short description of action (e.g. 'User Login'). |
| details | TEXT | Nullable | Detailed information. |
| created_at | DATETIME | Default NOW() | Timestamp of action. |

### Table: `issues`
| Field | Type | Constraint | Description |
| :--- | :--- | :--- | :--- |
| id | INT | PK, AI | Unique identifier for transaction. |
| user_id | INT | FK | References `users(id)`. |
| book_id | INT | FK | References `books(id)`. |
| status | ENUM | Issued/Returned| Current state of the loan. |

---

## 7. SOFTWARE DESCRIPTION

### 7.1 PHP & MVC
PHP serves as the core engine. The custom MVC framework provides clean routing through `.htaccess` and a centralized `App.php` router, ensuring that business logic is separated from presentation.

### 7.2 MySQL Database
The relational database ensures that data between users, books, and transactions remains linked (Foreign Key constraints) and consistent.

---

## 8. SYSTEM TESTING

### 8.1 Unit Testing
Individual modules like `Auth::checkAdmin()` and `BookModel::generateAccessionNumber()` are tested for expected outputs.

### 8.2 Integration Testing
Verifying the flow between the `Issue` model and the `Book` model to ensure that issuing a book correctly decrements the `available_quantity`.

### 8.3 UI/UX Testing
Ensuring that the Bootstrap-based responsive layout works across mobile and desktop browsers.

---

## 9. APPENDIX

### 9.1 Source Code (Key Excerpts)
**Book Addition Logic:**
```php
public function add($data) {
    if (empty($data['accession_no'])) {
        $data['accession_no'] = $this->generateAccessionNumber();
    }
    $this->db->query('INSERT INTO books (...) VALUES (...)');
    // ... binding and execution
}
```

### 9.2 Screen Layout Description
- **Admin Dashboard**: Sidebar navigation, summary cards (Total Books, Users, Fines).
- **Manage Books**: Searchable table with Edit/Delete buttons and an "Add New Book" selection.
- **User Portal**: Grid view of available books with a search bar.

---

## 10. CONCLUSION

The Library Management System successfully achieves its goal of replacing manual record-keeping with an efficient, scalable, and secure digital platform. The implementation of the MVC architecture ensures that the system is easy to maintain and expand in the future.

---

## 11. BIBLIOGRAPHY

- PHP Official Documentation: [php.net](https://www.php.net/)
- MySQL Reference Manual: [mysql.com](https://dev.mysql.com/doc/)
- Bootstrap Framework: [getbootstrap.com](https://getbootstrap.com/)
- MVC Design Patterns: "Design Patterns: Elements of Reusable Object-Oriented Software" by Gamma et al.
