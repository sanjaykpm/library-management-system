<?php
class AdminController extends Controller {
    public function __construct() {
        Auth::checkAdmin();
    }

    public function index() {
        redirect('admin/dashboard');
    }

    public function dashboard() {
        $bookModel = $this->model('Book');
        $userModel = $this->model('User');
        $issueModel = $this->model('Issue');
        $requestModel = $this->model('IssueRequest');
        $returnRequestModel = $this->model('ReturnRequest');
        $loggerModel = $this->model('Logger');
        
        $data = [
            'total_books' => $bookModel->getTotalBooks(),
            'total_users' => $userModel->getTotalUsers(),
            'total_issued' => $issueModel->getIssuedCount(),
            'pending_requests' => $requestModel->getTotalPendingRequests(),
            'pending_returns' => $returnRequestModel->getPendingCount(),
            'recent_activity' => $loggerModel->getRecentLogs(5)
        ];
        
        $this->view('admin/dashboard', $data);
    }

    public function change_password() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $userModel = $this->model('User');
            
            $data = [
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Validate
            if (empty($data['password'])) {
                $data['password_err'] = 'Please enter new password';
            } elseif (strlen($data['password']) < 6) {
                $data['password_err'] = 'Password must be at least 6 characters';
            }

            if (empty($data['confirm_password'])) {
                $data['confirm_password_err'] = 'Please confirm password';
            } else {
                if ($data['password'] !== $data['confirm_password']) {
                    $data['confirm_password_err'] = 'Passwords do not match';
                }
            }

            // Make sure errors are empty
            if (empty($data['password_err']) && empty($data['confirm_password_err'])) {
                // Hash Password
                $password = password_hash($data['password'], PASSWORD_DEFAULT);

                // Update Password
                if ($userModel->updatePassword($_SESSION['user_id'], $password)) {
                    flash('admin_password_success', 'Password changed successfully');
                    redirect('admin/dashboard');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('admin/change_password', $data);
            }
        } else {
            $data = [
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            $this->view('admin/change_password', $data);
        }
    }

    public function stats() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $issueModel = $this->model('Issue');
            $bookModel = $this->model('Book');

            $data = [
                'monthly_issues' => $issueModel->getMonthlyIssues(),
                'top_books' => $issueModel->getTopBooks(),
                'books_by_category' => $bookModel->getBooksByCategory()
            ];

            header('Content-Type: application/json');
            echo json_encode($data);
        }
    }
}
