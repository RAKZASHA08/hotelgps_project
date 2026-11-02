<?php
// Privacy & Security: Ensure session cookies are secure and protected
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once __DIR__ . '/db.php';

// Authentication Gate: Require receptionist or admin credentials
$userRole = $_SESSION['role'] ?? '';
$hotelId = $_SESSION['hotel_id'] ?? '';
$isAuth = !empty($_SESSION['reception_logged_in']) || ($userRole === 'receptionist' && !empty($hotelId)) || ($userRole === 'admin');

if (!$isAuth) {
    $branchParam = !empty($_GET['branch']) ? '?branch=' . urlencode($_GET['branch']) : '';
    header('Location: reception_login.php' . $branchParam);
    exit;
}

// Route directly to the Hotel Reception & Retailer View
require __DIR__ . '/app/Views/reception.view.php';

