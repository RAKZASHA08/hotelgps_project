<?php
// Privacy & Security: Ensure session cookies are secure and protected
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}
require_once __DIR__ . '/db.php';

require __DIR__ . '/app/Views/home.view.php';