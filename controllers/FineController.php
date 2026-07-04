<?php
class FineController extends Controller {
    private $fineModel;
    private $userModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->fineModel = $this->model('Fine');
        $this->userModel = $this->model('User');
    }

    public function index() {
        $this->manage();
    }

    public function manage() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $from_date = isset($_GET['from_date']) ? trim($_GET['from_date']) : '';
        $to_date = isset($_GET['to_date']) ? trim($_GET['to_date']) : '';
        $class = isset($_GET['class']) ? trim($_GET['class']) : '';
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $fines = $this->fineModel->getFinesWithFilters($limit, $offset, $search, $status, $from_date, $to_date, $class);
        $totalFinesFiltered = $this->fineModel->getTotalFinesCount($search, $status, $from_date, $to_date, $class);
        $totalPages = ceil($totalFinesFiltered / $limit);

        // Statistics
        $totalFineAmount = $this->fineModel->getTotalFinesAmount();
        $paidFineAmount = $this->fineModel->getTotalFinesAmount('paid');
        $unpaidFineAmount = $this->fineModel->getTotalFinesAmount('unpaid');
        $classWiseFines = $this->fineModel->getClassWiseFines();

        $data = [
            'fines' => $fines,
            'total_fine_amount' => $totalFineAmount,
            'paid_fine_amount' => $paidFineAmount,
            'unpaid_fine_amount' => $unpaidFineAmount,
            'class_wise_fines' => $classWiseFines,
            'search' => $search,
            'status' => $status,
            'from_date' => $from_date,
            'to_date' => $to_date,
            'class' => $class,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];

        $this->view('admin/fines/manage', $data);
    }

    public function update_status($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $status = $_POST['status'];
            
            if ($this->fineModel->updateFineStatus($id, $status)) {
                flash('fine_message', 'Fine status updated successfully');
            } else {
                flash('fine_message', 'Failed to update fine status', 'alert alert-danger');
            }
            redirect('fine/manage');
        }
    }
}
