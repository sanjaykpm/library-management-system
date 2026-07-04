<?php
class NotificationController extends Controller {
    private $notificationModel;

    public function __construct() {
        if (!isLoggedIn()) {
            redirect('auth/login');
        }
        $this->notificationModel = $this->model('Notification');
    }

    public function index() {
        $notifications = $this->notificationModel->getPendingNotifications($_SESSION['user_id']);
        
        $data = [
            'notifications' => $notifications
        ];

        $this->view('user/notification', $data);
    }

    public function mark_read($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->notificationModel->markAsRead($id, $_SESSION['user_id'])) {
                // If AJAX request, return JSON
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'Notification marked as read']);
                    exit;
                }
                redirect('notification/index');
            } else {
                die('Something went wrong');
            }
        }
    }
    
    public function mark_all_read() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if ($this->notificationModel->markAllAsRead($_SESSION['user_id'])) {
                flash('notification_msg', 'All notifications marked as read');
                redirect('notification/index');
            } else {
                die('Something went wrong');
            }
        }
    }
}
