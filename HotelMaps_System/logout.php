<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$redirect = $_GET['redirect'] ?? '';

// Completely unset and wipe session data
$_SESSION = [];

// Invalidate and delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

if ($redirect === 'admin') {
    header("Location: admin_login.php");
    exit;
}

header("Location: index.php");
exit;