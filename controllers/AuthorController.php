<?php
class AuthorController extends Controller {
    private $authorModel;

    public function __construct() {
        Auth::checkAdmin();
        $this->authorModel = $this->model('Author');
    }

    public function index() {
        redirect('author/manage');
    }

    public function manage() {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $authors = $this->authorModel->getAuthors($limit, $offset, $search);
        $totalAuthors = $this->authorModel->getTotalAuthors($search);
        $totalPages = ceil($totalAuthors / $limit);

        $data = [
            'authors' => $authors,
            'search' => $search,
            'current_page' => $page,
            'total_pages' => $totalPages
        ];
        $this->view('admin/authors/manage', $data);
    }

    public function add() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $data = [
                'name' => trim($_POST['name']),
                'bio' => trim($_POST['bio']),
                'name_err' => '',
                'bio_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter author name';
            }

            if (empty($data['name_err'])) {
                if ($this->authorModel->add($data)) {
                    flash('author_success', 'Author added successfully');
                    redirect('author/manage');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('admin/authors/add', $data);
            }
        } else {
            $data = [
                'name' => '',
                'bio' => '',
                'name_err' => '',
                'bio_err' => ''
            ];
            $this->view('admin/authors/add', $data);
        }
    }

    public function edit($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            $data = [
                'id' => $id,
                'name' => trim($_POST['name']),
                'bio' => trim($_POST['bio']),
                'name_err' => '',
                'bio_err' => ''
            ];

            if (empty($data['name'])) {
                $data['name_err'] = 'Please enter author name';
            }

            if (empty($data['name_err'])) {
                if ($this->authorModel->update($data)) {
                    flash('author_success', 'Author updated successfully');
                    redirect('author/manage');
                } else {
                    die('Something went wrong');
                }
            } else {
                $this->view('admin/authors/edit', $data);
            }
        } else {
            $author = $this->authorModel->getAuthorById($id);
            $data = [
                'id' => $id,
                'name' => $author->name,
                'bio' => $author->bio,
                'name_err' => '',
                'bio_err' => ''
            ];
            $this->view('admin/authors/edit', $data);
        }
    }

    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            if ($this->authorModel->delete($id)) {
                flash('author_success', 'Author Removed');
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
                echo json_encode(['success' => true, 'message' => 'Author removed successfully.']);
                exit;
            }

            redirect('author/manage');
        }
    }
}
