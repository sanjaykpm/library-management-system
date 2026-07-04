<?php
class UserController extends Controller {
    public function __construct() {
        Auth::checkLogged();
    }

    public function index() {
        redirect('user/dashboard');
    }

    public function dashboard() {
        $bookModel = $this->model('Book');
        $issueModel = $this->model('Issue');
        $returnRequestModel = $this->model('ReturnRequest');
        $fineModel = $this->model('Fine');
        $categoryModel = $this->model('Category'); // Assuming Category model exists
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $category_id = isset($_GET['category']) ? trim($_GET['category']) : '';
        
        // Fetch Books with optional Search & Filter
        // Note: You might need to update getBooks to support category filtering
        // For now, fetching standard books. 
        if ($search) {
            $books = $bookModel->getBooks(12, 0, $search); // Increase limit for grid view
        } else {
            $books = $bookModel->getBooks(12, 0);
        }

        // Summary Cards Data
        $totalAvailable = $bookModel->getTotalAvailableQuantity();
        $myIssuedBooksCount = $issueModel->getUserIssuedCount($_SESSION['user_id']);
        $pendingReturnCount = $returnRequestModel->getUserPendingCount($_SESSION['user_id']);
        $totalFine = $fineModel->getUserTotalFine($_SESSION['user_id']);

        // Notifications Data
        $dueSoonBooks = $issueModel->getBooksDueSoon($_SESSION['user_id'], 3);
        $overdueCount = $issueModel->getOverdueBooksCount($_SESSION['user_id']);

        $data = [
            'books' => $books,
            'search' => $search,
            'categories' => $bookModel->getCategories(),
            'stats' => [
                'total_available' => $totalAvailable,
                'my_issued' => $myIssuedBooksCount,
                'pending_return' => $pendingReturnCount,
                'total_fine' => $totalFine
            ],
            'notifications' => [
                'due_soon' => $dueSoonBooks,
                'overdue_count' => $overdueCount,
                'has_fine' => $totalFine > 0
            ]
        ];
        $this->view('user/dashboard', $data);
    }

    public function request_book($book_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $requestModel = $this->model('IssueRequest');
            
            if ($requestModel->createRequest($_SESSION['user_id'], $book_id)) {
                flash('request_message', 'Book requested successfully');
            } else {
                flash('request_message', 'Request already exists or failed', 'alert-danger');
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            redirect('user/dashboard');
        }
    }

    public function my_books() {
        $issueModel = $this->model('Issue');
        $fineModel = $this->model('Fine');

        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;
        
        // This getUserIssuedBooks need modification to support pagination if the list is huge
        // For now, fetching all and slicing in PHP or optimally update model
        // Ideally: $issueModel->getUserIssuedBooksPaginated($user_id, $limit, $offset);
        
        $allIssuedBooks = $issueModel->getUserIssuedBooks($_SESSION['user_id']);
        $totalBooks = count($allIssuedBooks);
        $totalPages = ceil($totalBooks / $limit);
        $issuedBooks = array_slice($allIssuedBooks, $offset, $limit);
        
        // Summary Stats
        $activeCount = $issueModel->getUserIssuedCount($_SESSION['user_id']);
        $returnedCount = $issueModel->getUserReturnedCount($_SESSION['user_id']);
        $overdueCount = $issueModel->getOverdueBooksCount($_SESSION['user_id']);
        $totalBorrowed = count($allIssuedBooks); // or defined as distinct historical count if needed

        $data = [
            'issued_books' => $issuedBooks,
            'current_page' => $page,
            'total_pages' => $totalPages,
            'stats' => [
                'total_borrowed' => $totalBorrowed, // Available history count
                'active' => $activeCount,
                'returned' => $returnedCount,
                'overdue' => $overdueCount
            ]
        ];
        $this->view('user/my_books', $data);
    }

    public function request_return($issue_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $returnRequestModel = $this->model('ReturnRequest');
            
            if ($returnRequestModel->createRequest($issue_id, $_SESSION['user_id'])) {
                flash('return_message', 'Return request submitted successfully', 'alert-success');
            } else {
                flash('return_message', 'Request already exists or failed', 'alert-danger');
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }

            redirect('user/my_books');
        }
    }

    public function profile() {
        $userModel = $this->model('User');
        $user = $userModel->getUserById($_SESSION['user_id']);
        
        $data = [
            'user' => $user,
            'password_err' => ''
        ];
        $this->view('user/profile', $data);
    }

    public function edit_profile() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $userModel = $this->model('User');
            $data = [
                'id' => $_SESSION['user_id'],
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'class' => trim($_POST['class']),
                'mobile_no' => trim($_POST['mobile_no']),
                'password' => trim($_POST['password']),
                'password_err' => ''
            ];

            // Update Profile
            if ($userModel->updateProfile($data)) {
                $_SESSION['user_name'] = $data['name'];
                
                // Update Password if not empty
                if (!empty($data['password'])) {
                    if (strlen($data['password']) < 6) {
                        $data['password_err'] = 'Password must be at least 6 characters';
                    } else {
                        $hashed_password = password_hash($data['password'], PASSWORD_DEFAULT);
                        $userModel->updatePassword($data['id'], $hashed_password);
                    }
                }

                if (empty($data['password_err'])) {
                    flash('profile_message', 'Profile Updated Successfully');
                    redirect('user/profile');
                } else {
                    $data['user'] = $userModel->getUserById($_SESSION['user_id']);
                    $this->view('user/profile', $data);
                }
            }
        }
    }
}
