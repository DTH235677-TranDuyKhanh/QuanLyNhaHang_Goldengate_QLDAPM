<?php
// Cấu hình hệ thống - chỉnh theo máy của bạn
return [
    'db' => [
        'host' => 'localhost',
        'port' => 3306,
        'name' => 'goldengate',
        'user' => 'root',
        'pass' => '',          // XAMPP mặc định rỗng
    ],
    'site_name'      => 'GoldenGate',
    'debug'          => true,   // đặt false khi triển khai thật
    'free_ship_from' => 300000, // miễn phí giao hàng từ mức này
    'ship_fee'       => 15000,
];
