<?php
/**
 * Navigation and Session Helpers
 */

// Start session
session_start();

// Flash message helper
// EXAMPLE - flash('register_success', 'You are now registered');
// DISPLAY IN VIEW - echo flash('register_success');
function flash($name = '', $message = '', $class = 'alert alert-success') {
    if (!empty($name)) {
        if (!empty($message)) {
            // If message is set, we are SETTING a new flash message
            // Overwrite existing message and class even if already set
            $_SESSION[$name] = $message;
            $_SESSION[$name . '_class'] = $class;
        } elseif (empty($message) && !empty($_SESSION[$name])) {
            // If message is empty, we are DISPLAYING the flash message
            $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
            // Use a unique ID based on name and a reusable class
            echo '<div class="' . $class . ' fade show msg-flash" id="' . $name . '-flash">' . $_SESSION[$name] . '</div>';
            unset($_SESSION[$name]);
            unset($_SESSION[$name . '_class']);
        }
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return (isset($_SESSION['user_role']) && $_SESSION['user_role'] == ROLE_ADMIN);
}

function time_elapsed_string($datetime, $full = false) {
    if ($datetime == '0000-00-00 00:00:00' || empty($datetime)) return "N/A";
    
    $now = new DateTime;
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }

    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}
