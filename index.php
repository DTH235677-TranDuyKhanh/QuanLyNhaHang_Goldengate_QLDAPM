<?php
// Kết nối cơ sở dữ liệu
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'restaurant_db';

$conn = new mysqli($host, $username, $password, $database);
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");

// Lấy danh mục đang chọn (nếu có)
$category_id = isset($_GET['category_id']) ? intval($_GET['category_id']) : 0;

// Truy vấn danh sách danh mục
$categories_result = $conn->query("SELECT * FROM categories");

// Truy vấn danh sách món ăn (có lọc theo danh mục nếu được chọn)
if ($category_id > 0) {
    $foods_sql = "SELECT f.*, c.category_name FROM foods f 
                  JOIN categories c ON f.category_id = c.category_id 
                  WHERE f.category_id = $category_id";
} else {
    $foods_sql = "SELECT f.*, c.category_name FROM foods f 
                  JOIN categories c ON f.category_id = c.category_id";
}
$foods_result = $conn->query($foods_sql);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Thực đơn - Quản lý Nhà hàng</title>
    <!-- Nhúng Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome icon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .food-card {
            transition: transform 0.2s;
        }
        .food-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .food-img {
            height: 200px;
            object-fit: cover;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-utensils me-2"></i>Restaurant Manager</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="index.php">Thực đơn</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Bàn ăn</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Đơn hàng</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Nội dung chính -->
    <div class="container my-4">
        <div class="row">
            <!-- Cột bên trái: Danh mục món ăn -->
            <div class="col-lg-3 mb-4">
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white fw-bold">
                        <i class="fas fa-list me-2"></i>Danh mục món
                    </div>
                    <div class="list-group list-group-flush">
                        <a href="index.php" class="list-group-item list-group-item-action <?= ($category_id == 0) ? 'active' : ''; ?>">
                            Tất cả món ăn
                        </a>
                        <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                            <?php while ($cat = $categories_result->fetch_assoc()): ?>
                                <a href="index.php?category_id=<?= $cat['category_id']; ?>" 
                                   class="list-group-item list-group-item-action <?= ($category_id == $cat['category_id']) ? 'active' : ''; ?>">
                                    <?= htmlspecialchars($cat['category_name']); ?>
                                </a>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Cột bên phải: Hiển thị danh sách món ăn -->
            <div class="col-lg-9">
                <h3 class="mb-4 text-secondary">Danh sách thực đơn</h3>
                <div class="row">
                    <?php if ($foods_result && $foods_result->num_rows > 0): ?>
                        <?php while ($food = $foods_result->fetch_assoc()): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card food-card h-100 shadow-sm">
                                    <!-- Ảnh món ăn -->
                                    <img src="<?= !empty($food['image']) ? htmlspecialchars($food['image']) : 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                                         class="card-img-top food-img" alt="<?= htmlspecialchars($food['food_name']); ?>">
                                    
                                    <div class="card-body d-flex flex-column">
                                        <span class="badge bg-secondary mb-2 align-self-start"><?= htmlspecialchars($food['category_name']); ?></span>
                                        <h5 class="card-title fw-bold"><?= htmlspecialchars($food['food_name']); ?></h5>
                                        <p class="card-text text-danger fw-bold fs-5 mb-2">
                                            <?= number_format($food['price'], 0, ',', '.'); ?> đ
                                        </p>
                                        
                                        <div class="mt-auto">
                                            <?php if ($food['status'] == 'available'): ?>
                                                <span class="badge bg-success mb-2">Còn món</span>
                                                <button class="btn btn-outline-primary w-100 btn-sm" onclick="alert('Đã thêm <?= htmlspecialchars($food['food_name']); ?> vào gọi món!')">
                                                    <i class="fas fa-cart-plus me-1"></i> Chọn món
                                                </button>
                                            <?php else: ?>
                                                <span class="badge bg-danger mb-2">Hết món</span>
                                                <button class="btn btn-secondary w-100 btn-sm" disabled>Tạm hết</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-warning text-center">Không có món ăn nào trong danh mục này.</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php $conn->close(); ?>