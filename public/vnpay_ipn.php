<?php
require __DIR__ . '/../core/bootstrap.php';

header('Content-Type: application/json; charset=utf-8');

function vnpay_ipn_response($code, $message) {
    echo json_encode(['RspCode' => $code, 'Message' => $message], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    vnpay_ipn_response('99', 'Invalid request method');
}
if (!VnPay::xacThucChuKy($_GET)) {
    vnpay_ipn_response('97', 'Invalid signature');
}
if (!VnPay::thongBaoThuongNhanHopLe($_GET)) {
    vnpay_ipn_response('04', 'Invalid merchant or currency');
}

try {
    $code = DonHang::xuLyThongBaoVnPay($_GET);
    $messages = [
        '00' => 'Confirm Success',
        '01' => 'Order not found',
        '02' => 'Order already confirmed or invalid state',
        '04' => 'Invalid amount or transaction data',
    ];
    vnpay_ipn_response($code, $messages[$code] ?? 'Unknown error');
} catch (Throwable $e) {
    error_log('VNPay IPN processing failed: ' . $e->getMessage());
    vnpay_ipn_response('99', 'Internal processing error');
}
