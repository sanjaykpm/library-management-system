<?php
class CategoryController extends Controller {
    private $categoryModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->categoryModel = $this->model('Category');
    }

    public function index() {
        redirect('category/manage');
    }

    public function manage() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $categories = $this->categoryModel->getCategories($limit, $offset, $search);
        $totalCategories = $this->categoryModel->getTotalCategories($search);
        $totalPages = ceil($totalCategories / $limit);

        $data = [
            'categories' => $categories,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        $this->view('admin/categories/manage', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $name = trim($_POST['name']);
            if (!empty($name)) {
                if ($this->categoryModel->add($name)) {
                    flash('category_success', 'Category added successfully');
                    redirect('category/manage');
                }
            }
        } else {
            $this->view('admin/categories/add');
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $name = trim($_POST['name']);
            if (!empty($name)) {
                if ($this->categoryModel->update($id, $name)) {
                    flash('category_success', 'Category updated successfully');
                    redirect('category/manage');
                } else {
                    die('Something went wrong');
                }
            }
        } else {
            $category = $this->categoryModel->getCategoryById($id);
            $data = ['category' => $category];
            $this->view('admin/categories/edit', $data);
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            if ($this->categoryModel->delete($id)) {
                flash('category_success', 'Category Removed');
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
                echo json_encode(['success' => true, 'message' => 'Category removed successfully.']);
                exit;
            }

            redirect('category/manage');
        }
    }
}
