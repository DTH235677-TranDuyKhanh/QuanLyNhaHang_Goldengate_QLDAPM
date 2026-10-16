<?php
// Cho phép gọi API từ các nguồn khác (CORS) nếu tách biệt Frontend/Backend
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');

require_once '../config/db.php';

try {
    // Truy vấn lấy danh sách món ăn kết hợp với bảng danh mục
    $sql = "SELECT f.food_id, f.food_name, f.price, f.status, f.image, c.category_name 
            FROM foods f 
            JOIN categories c ON f.category_id = c.category_id";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $foods = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => $foods
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
?>