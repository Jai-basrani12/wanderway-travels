<?php
// WanderWay Travels - Admin Authentication Check Middleware

// Ensure configuration is loaded so BASE_URL and session are initialized
if (!defined('BASE_URL')) {
    require_once __DIR__ . '/../config/config.php';
}

// Redirect to login page if admin is not authenticated in session
if (!isset($_SESSION['admin_id'])) {
    header("Location: " . BASE_URL . "admin/login.php");
    exit;
}
