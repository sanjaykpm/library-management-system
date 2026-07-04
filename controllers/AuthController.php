<?php
class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        $this->userModel = $this->model('User');
    }

    public function index() {
        $this->login();
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Process form
            $data = [
                'reg_no' => trim($_POST['reg_no']),
                'class' => trim($_POST['class']),
                'mobile_no' => trim($_POST['mobile_no']),
                'name' => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'confirm_password' => trim($_POST['confirm_password']),
                'role_id' => ROLE_USER,
                'student_id_err' => '',
                'reg_no_err' => '',
                'class_err' => '',
                'mobile_no_err' => '',
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];

            // Validate
            // Validate
            // Auto generate Student ID
            $data['student_id'] = $this->userModel->generateStudentId();

            if (empty($data['reg_no'])) {
                $data['reg_no_err'] = 'Please enter Registration Number';
            } 
            // Check for Reg No uniqueness if logic exists in model (skipping explicit check for now to allow simple flow, or add check if needed)
            
            if (empty($data['class'])) $data['class_err'] = 'Please enter Class';
            if (empty($data['mobile_no'])) $data['mobile_no_err'] = 'Please enter Mobile Number';

            if (empty($data['email'])) $data['email_err'] = 'Please enter email';
            elseif ($this->userModel->findUserByEmail($data['email'])) $data['email_err'] = 'Email is already taken';

            if (empty($data['name'])) $data['name_err'] = 'Please enter name';
            if (empty($data['password'])) $data['password_err'] = 'Please enter password';
            elseif (strlen($data['password']) < 6) $data['password_err'] = 'Password must be at least 6 characters';

            if ($data['password'] !== $data['confirm_password']) $data['confirm_password_err'] = 'Passwords do not match';

            // Make sure errors are empty
            if (empty($data['student_id_err']) && empty($data['reg_no_err']) && empty($data['class_err']) && empty($data['mobile_no_err']) && empty($data['email_err']) && empty($data['name_err']) && empty($data['password_err']) && empty($data['confirm_password_err'])) {
                // Hash Password
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);

                // Register User
                if ($this->userModel->register($data)) {
                    flash('register_success', 'You are registered and can log in');
                    redirect('auth/login');
                } else {
                    die('Something went wrong');
                }
            } else {
                // Load view with errors
                $this->view('auth/register', $data);
            }

        } else {
            // Init data
            $data = [
                'student_id' => '',
                'reg_no' => '',
                'class' => '',
                'mobile_no' => '',
                'name' => '',
                'email' => '',
                'password' => '',
                'confirm_password' => '',
                'student_id_err' => '',
                'reg_no_err' => '',
                'class_err' => '',
                'mobile_no_err' => '',
                'name_err' => '',
                'email_err' => '',
                'password_err' => '',
                'confirm_password_err' => ''
            ];
            $this->view('auth/register', $data);
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();
            
            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'email_err' => '',
                'password_err' => ''
            ];

            if (empty($data['email'])) $data['email_err'] = 'Please enter email';
            if (empty($data['password'])) $data['password_err'] = 'Please enter password';

            if (empty($data['email_err']) && empty($data['password_err'])) {
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);
                if ($loggedInUser) {
                    $this->createUserSession($loggedInUser);
                    
                    // Log Login
                    $logger = $this->model('Logger');
                    $logger->log('User Login', 'User logged in: ' . $loggedInUser->email);
                    
                    if ($loggedInUser->role_id == ROLE_ADMIN) {
                        redirect('admin/dashboard');
                    } else {
                        redirect('user/dashboard');
                    }
                } else {
                    $data['password_err'] = 'Invalid email or password';
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $data = [
                'email' => '',
                'password' => '',
                'login_type' => 'student',
                'email_err' => '',
                'password_err' => ''
            ];
            $this->view('auth/login', $data);
        }
    }

    public function admin_login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->verifyCsrf();

            $data = [
                'email' => trim($_POST['email']),
                'password' => trim($_POST['password']),
                'login_type' => 'admin',
                'email_err' => '',
                'password_err' => ''
            ];

            if (empty($data['email'])) $data['email_err'] = 'Please enter admin email';
            if (empty($data['password'])) $data['password_err'] = 'Please enter password';

            if (empty($data['email_err']) && empty($data['password_err'])) {
                $loggedInUser = $this->userModel->login($data['email'], $data['password']);
                if ($loggedInUser && $loggedInUser->role_id == ROLE_ADMIN) {
                    $this->createUserSession($loggedInUser);
                } else {
                    $data['password_err'] = 'Invalid admin credentials';
                    $this->view('auth/login', $data);
                }
            } else {
                $this->view('auth/login', $data);
            }
        } else {
            $data = [
                'email' => '',
                'password' => '',
                'login_type' => 'admin',
                'email_err' => '',
                'password_err' => ''
            ];
            $this->view('auth/login', $data);
        }
    }

    public function createUserSession($user) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = $user->id;
        $_SESSION['user_email'] = $user->email;
        $_SESSION['user_name'] = $user->name;
        $_SESSION['user_role'] = $user->role_id;
        
        if ($user->role_id == ROLE_ADMIN) {
            redirect('admin/dashboard');
        } else {
            redirect('user/dashboard');
        }
    }

    public function logout() {
        unset($_SESSION['user_id']);
        unset($_SESSION['user_email']);
        unset($_SESSION['user_name']);
        unset($_SESSION['user_role']);
        session_destroy();
        redirect('auth/login');
    }
}
