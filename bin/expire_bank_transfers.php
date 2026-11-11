<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../core/bootstrap.php';
$count = DonHang::hetHanChuyenKhoan();
echo 'Expired bank transfer orders: ' . $count . PHP_EOL;
