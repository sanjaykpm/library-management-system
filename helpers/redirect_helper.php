<?php
/**
 * Redirect Helper
 */
function redirect($page) {
    header('location: ' . URLROOT . '/' . $page);
    exit();
}
