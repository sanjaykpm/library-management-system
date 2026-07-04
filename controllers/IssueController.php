<?php
class IssueController extends Controller {
    private $issueModel;
    private $bookModel;
    private $userModel;
    private $requestModel;
    private $notificationModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->issueModel = $this->model('Issue');
        $this->bookModel = $this->model('Book');
        $this->userModel = $this->model('User');
        $this->requestModel = $this->model('IssueRequest');
        $this->notificationModel = $this->model('Notification');
    }

    public function index() {
        redirect('issue/manage');
    }

    public function manage() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $overdue = isset($_GET['overdue']) && $_GET['overdue'] == '1' ? true : false;
        $from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
        $to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';
        $class = isset($_GET['class']) ? trim($_GET['class']) : '';
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $issues = $this->issueModel->getIssuedBooks($limit, $offset, $search, $status, $overdue, $from_date, $to_date, $class);
        $totalIssues = $this->issueModel->getTotalIssuedBooks($search, $status, $overdue, $from_date, $to_date, $class);
        $totalPages = ceil($totalIssues / $limit);

        $data = [
            'issues' => $issues,
            'search' => $search,
            'status' => $status,
            'overdue' => $overdue,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'class' => $class,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        $this->view('admin/issue/manage', $data);
    }

    public function issue_book() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            
            $accession_no = trim($_POST['accession_no']);
            $student_id = trim($_POST['student_id']);
            $return_date = trim($_POST['return_date']);

            // Find Book
            $book = $this->bookModel->findBookByAccession($accession_no);
            // Find User
            $user = $this->userModel->findUserByStudentId($student_id);

            $data = [
                'accession_no' => $accession_no,
                'student_id' => $student_id,
                'return_date' => $return_date,
                'error' => ''
            ];

            if (!$book) {
                $data['error'] = 'Book with this Accession Number not found';
            } elseif ($book->available_quantity <= 0) {
                $data['error'] = 'Book is currently not available';
            } elseif (!$user) {
                $data['error'] = 'Student with this ID not found';
            }

            if (empty($data['error'])) {
                $issueData = [
                    'user_id' => $user->id,
                    'book_id' => $book->id,
                    'issue_date' => date('Y-m-d'),
                    'return_date' => $return_date
                ];

                try {
                    $this->issueModel->beginTransaction();
                    
                    if ($this->issueModel->issueBook($issueData)) {
                        
                        // Log
                        $logger = $this->model('Logger');
                        $logger->log('Issue Book', "Issued book {$book->accession_no} to {$user->student_id}");

                        $this->issueModel->commit();

                        flash('issue_success', 'Book issued successfully');
                        redirect('issue/manage');
                    } else {
                        throw new Exception("Failed to issue book");
                    }
                } catch (Exception $e) {
                    $this->issueModel->rollBack();
                    die('Something went wrong: ' . $e->getMessage());
                }
            } else {
                $this->view('admin/issue/issue-book', $data);
            }
        } else {
            $data = [
                'accession_no' => '',
                'student_id' => '',
                'return_date' => '',
                'error' => ''
            ];
            $this->view('admin/issue/issue-book', $data);
        }
    }

    public function return_book($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            
            try {
                $this->issueModel->beginTransaction();
                
                // Handle Fines (Calculate while status is still 'issued')
                $fineModel = $this->model('Fine');
                $issue = $this->issueModel->getIssueById($id);
                
                if ($issue && $issue->status == 'issued') {
                    $fineAmount = $fineModel->calculateFine($id);
                    if ($fineAmount > 0) {
                        $fineModel->createFine($issue->user_id, $id, $fineAmount);
                    }
                }

                if ($this->issueModel->returnBook($id)) {
                    // Log
                    $logger = $this->model('Logger');
                    $logger->log('Return Book', "Returned issue ID: {$id}");

                    $this->issueModel->commit();

                    flash('issue_success', 'Book returned successfully');
                } else {
                    throw new Exception("Failed to return book");
                }
            } catch (Exception $e) {
                $this->issueModel->rollBack();
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                    exit;
                }
                die('Something went wrong: ' . $e->getMessage());
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Book returned successfully.']);
                exit;
            }

            redirect('issue/manage');
        } else {
            redirect('issue/manage');
        }
    }

    public function requests() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $requests = $this->requestModel->getPendingRequests($limit, $offset, $search);
        $totalRequests = $this->requestModel->getTotalPendingRequests($search);
        $totalPages = ceil($totalRequests / $limit);

        $data = [
            'requests' => $requests,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        $this->view('admin/issue/requests', $data);
    }

    public function approve_request($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            
            $request = $this->requestModel->getRequestById($id);
            if ($request) {
                // Issue the book
                $book = $this->bookModel->getBookById($request->book_id);
                if ($book->available_quantity > 0) {
                    $issueData = [
                        'user_id' => $request->user_id,
                        'book_id' => $request->book_id,
                        'issue_date' => date('Y-m-d'),
                        'return_date' => date('Y-m-d', strtotime('+15 days')) // Default return date
                    ];

                    try {
                        $this->issueModel->beginTransaction();

                        if ($this->issueModel->issueBook($issueData)) {
                            $this->requestModel->updateStatus($id, 'approved');
                            
                            // Log
                            $logger = $this->model('Logger');
                            $logger->log('Approve Issue Request', "Approved request ID: {$id}");

                            // Notify Student
                            $this->notificationModel->create(
                                $request->user_id, 
                                "Your request for book '{$book->title}' has been approved.", 
                                'success'
                            );

                            $this->issueModel->commit();

                            flash('request_success', 'Request approved and book issued.');
                            $message = 'Request approved and book issued.';
                        } else {
                            throw new Exception("Failed to issue book during approval");
                        }
                    } catch (Exception $e) {
                        $this->issueModel->rollBack();
                        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                            header('Content-Type: application/json');
                            echo json_encode(['success' => false, 'message' => 'Failed to issue book.']);
                            exit;
                        }
                        flash('request_danger', 'Failed to issue book.', 'alert alert-danger');
                    }
                } else {
                    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'message' => 'Book not available.']);
                        exit;
                    }
                    flash('request_danger', 'Book not available.', 'alert alert-danger');
                }
            } else {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Request not found.']);
                    exit;
                }
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message ?? 'Request approved.']);
                exit;
            }

            redirect('issue/requests');
        }
    }

    public function reject_request($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            
            if ($this->requestModel->updateStatus($id, 'rejected')) {
                // Log
                $logger = $this->model('Logger');
                $logger->log('Reject Issue Request', "Rejected request ID: {$id}");
                
                // Notify Student
                $request = $this->requestModel->getRequestById($id);
                if($request) {
                    $this->notificationModel->create(
                        $request->user_id, 
                        "Your request for book '{$request->book_title}' has been rejected.", 
                        'danger'
                    );
                }

                flash('request_success', 'Request rejected.');
                $message = 'Request rejected.';
            } else {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Failed to reject request.']);
                    exit;
                }
                flash('request_danger', 'Something went wrong.', 'alert alert-danger');
                $message = 'Something went wrong.';
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message]);
                exit;
            }

            redirect('issue/requests');
        }
    }

    public function return_requests() {
        $returnRequestModel = $this->model('ReturnRequest');
        
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $requests = $returnRequestModel->getPendingRequests($limit, $offset, $search);
        $totalRequests = $returnRequestModel->getTotalPendingRequests($search);
        $totalPages = ceil($totalRequests / $limit);

        $data = [
            'requests' => $requests,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        $this->view('admin/issue/return_requests', $data);
    }

    public function approve_return($request_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $returnRequestModel = $this->model('ReturnRequest');
            $fineModel = $this->model('Fine');
            
            $request = $returnRequestModel->getRequestById($request_id);
            
            if ($request) {
                try {
                    $this->issueModel->beginTransaction();
                    $fineAmount = $fineModel->calculateFine($request->issue_id);
                    
                    if ($fineAmount > 0) {
                        $fineModel->createFine($request->user_id, $request->issue_id, $fineAmount);
                    }
                    
                    if ($this->issueModel->returnBook($request->issue_id)) {
                        $returnRequestModel->updateStatus($request_id, 'approved');
                        
                        if ($fineAmount > 0) {
                            flash('return_request_message', "Return approved. Fine of ₹{$fineAmount} has been applied.", 'alert alert-warning');
                            $message = "Return approved. Fine of ₹{$fineAmount} applied.";
                            
                            $this->notificationModel->create(
                                $request->user_id, 
                                "Return approved for '{$request->book_title}'. Fine of ₹{$fineAmount} applied.", 
                                'warning'
                            );
                        } else {
                            flash('return_request_message', 'Return approved successfully', 'alert alert-success');
                            $message = 'Return approved successfully.';
                            
                            $this->notificationModel->create(
                                $request->user_id, 
                                "Return approved for '{$request->book_title}'. No fine.", 
                                'success'
                            );
                        }
                        
                        $this->issueModel->commit();
                    } else {
                        throw new Exception("Failed to process return");
                    }
                } catch (Exception $e) {
                    $this->issueModel->rollBack();
                    flash('return_request_message', 'Failed to process return: ' . $e->getMessage(), 'alert alert-danger');
                    $message = 'Failed to process return.';
                }
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message ?? 'Return approved.']);
                exit;
            }

            redirect('issue/return_requests');
        }
    }

    public function reject_return($request_id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $returnRequestModel = $this->model('ReturnRequest');
            
            if ($returnRequestModel->updateStatus($request_id, 'rejected')) {
                // Get request details for notification
                 $request = $returnRequestModel->getRequestById($request_id);
                 if($request) {
                     $this->notificationModel->create(
                         $request->user_id, 
                         "Your return request for '{$request->book_title}' has been rejected. Please contact admin.", 
                         'danger'
                     );
                 }

                flash('return_request_message', 'Return request rejected', 'alert alert-success');
                $message = 'Return request rejected.';
            } else {
                flash('return_request_message', 'Failed to reject request', 'alert alert-danger');
                $message = 'Failed to reject request.';
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => $message]);
                exit;
            }

            redirect('issue/return_requests');
        }
    }

    public function export() {
        Auth::checkAdmin();
        $issues = $this->issueModel->getIssuedBooks(10000); // Get all (limit high)

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="issue_history.csv"');

        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'User', 'Student ID', 'Book', 'Accession No', 'Issue Date', 'Return Date', 'Status', 'Actual Return Date']);

        foreach ($issues as $issue) {
            fputcsv($output, [
                $issue->id,
                $issue->user_name,
                $issue->student_id,
                $issue->book_title,
                $issue->accession_no,
                $issue->issue_date,
                $issue->return_date,
                $issue->status,
                $issue->actual_return_date ?? 'N/A'
            ]);
        }
        fclose($output);
        exit;
    }
}
