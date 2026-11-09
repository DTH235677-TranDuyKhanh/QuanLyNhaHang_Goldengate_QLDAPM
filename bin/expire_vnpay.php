<?php
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require __DIR__ . '/../core/bootstrap.php';
$count = DonHang::hetHanThanhToanVnPay();
echo 'Expired VNPay orders: ' . $count . PHP_EOL;
