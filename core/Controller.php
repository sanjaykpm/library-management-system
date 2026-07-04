<?php
/**
 * Base Controller
 * Loads Models and Views
 */
class Controller {
    // Load model
    public function model($model) {
        // Require model file
        require_once 'models/' . $model . '.php';

        // Instantiate model
        return new $model();
    }

    // Load view
    public function view($view, $data = []) {
        // Check for view file
        if (file_exists('views/' . $view . '.php')) {
            require_once 'views/' . $view . '.php';
        } else {
            // View does not exist
            die('View does not exist');
        }
    }

    protected function verifyCsrf() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            if (!isset($_POST['csrf_token']) || !Csrf::verifyToken($_POST['csrf_token'])) {
                die('CSRF Token Verification Failed');
            }
        }
    }
}
