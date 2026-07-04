<?php
/**
 * Auth Core Class
 * Handles role-based access control
 */
class Auth {
    public static function checkAdmin() {
        if (!isAdmin()) {
            flash('auth_error', 'Access Denied: Admin privileges required', 'alert alert-danger');
            redirect('auth/login');
            exit();
        }
    }

    public static function checkLogged() {
        if (!isLoggedIn()) {
            flash('auth_error', 'Please log in to continue', 'alert alert-danger');
            redirect('auth/login');
            exit();
        }
    }
}
