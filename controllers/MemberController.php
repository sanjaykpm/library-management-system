<?php
class MemberController extends Controller {
    private $userModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->userModel = $this->model('User');
    }

    public function index() {
        redirect('member/manage');
    }

    public function manage() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $users = $this->userModel->getUsers($limit, $offset, $search);
        $totalUsers = $this->userModel->getTotalUsers($search);
        $totalPages = ceil($totalUsers / $limit);

        $data = [
            'users' => $users,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        $this->view('admin/users/manage', $data);
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            if ($this->userModel->deleteUser($id)) {
                flash('member_success', 'Member Removed');
            } else {
                if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Something went wrong']);
                    exit;
                }
                die('Something went wrong');
            }

            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'Member removed successfully.']);
                exit;
            }

            redirect('member/manage');
        }
    }
}
