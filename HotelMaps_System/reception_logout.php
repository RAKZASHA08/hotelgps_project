<?php
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

unset($_SESSION['reception_logged_in']);
unset($_SESSION['hotel_id']);

// Clear receptionist credentials if not a main system admin
if (($_SESSION['role'] ?? '') === 'receptionist') {
    unset($_SESSION['user_id']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_email']);
    unset($_SESSION['role']);
}

header('Location: reception_login.php?logged_out=1');
exit;
