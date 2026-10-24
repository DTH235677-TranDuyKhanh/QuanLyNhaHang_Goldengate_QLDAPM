<?php
if (session_status() === PHP_SESSION_NONE) {
    // Tên phiên riêng để không lẫn với các dự án PHP khác chạy trên cùng localhost
    session_name('GGSESSID');
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}
date_default_timezone_set('Asia/Ho_Chi_Minh');
define('ROOT', dirname(__DIR__));
$GLOBALS['config'] = require ROOT . '/config/config.php';

spl_autoload_register(function ($class) {
    $f = ROOT . '/model/' . strtolower($class) . '.php';
    if (is_file($f)) require_once $f;
});
require_once ROOT . '/core/helpers.php';

set_exception_handler(function ($e) {
    error_log((string)$e);
    http_response_code(500);
    echo '<h3>Đã xảy ra lỗi hệ thống. Vui lòng thử lại sau.</h3>';
    if (cfg('debug')) echo '<pre>' . htmlspecialchars((string)$e) . '</pre>';
});
