<?php
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../core/bootstrap.php';

try {
    $foods = DB::all("SELECT m.id AS food_id, m.tenmon AS food_name, m.giaban AS price,
                             CASE WHEN m.trangthai = 1 THEN 'available' ELSE 'sold_out' END AS status,
                             m.hinhanh AS image, n.tennhom AS category_name
                      FROM monan m
                      JOIN nhomon n ON n.id = m.nhom_id
                      WHERE m.trangthai = 1
                      ORDER BY m.id");
    echo json_encode([
        'status' => 'success',
        'data' => $foods
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Không thể tải danh sách món ăn.'
    ], JSON_UNESCAPED_UNICODE);
}