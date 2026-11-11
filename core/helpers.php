<?php
function cfg($key) { return $GLOBALS['config'][$key] ?? null; }

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function money($n) { return number_format((float)$n, 0, ',', '.') . 'đ'; }
function redirect($url) { header('Location: ' . $url); exit; }
function post($k, $d = '') { return isset($_POST[$k]) ? trim((string)$_POST[$k]) : $d; }
function maDon($id) { return 'GG' . str_pad((string)$id, 6, '0', STR_PAD_LEFT); }

// ----- CSRF -----
function csrf_token() {
    if (empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['_csrf'];
}
function csrf_field() { return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">'; }
function csrf_verify() {
    $t = $_POST['_csrf'] ?? '';
    if (!is_string($t) || !hash_equals($_SESSION['_csrf'] ?? '', $t)) {
        http_response_code(419);
        die('Phiên làm việc không hợp lệ. Vui lòng quay lại và thử lại.');
    }
}

// ----- Flash message -----
function flash($type, $msg) { $_SESSION['_flash'][] = [$type, $msg]; }
function take_flash() { $f = $_SESSION['_flash'] ?? []; unset($_SESSION['_flash']); return $f; }

// ----- Người dùng -----
function current_user() {
    $u = $_SESSION['user'] ?? null;
    // Chỉ chấp nhận dữ liệu đúng cấu trúc của GoldenGate
    return (is_array($u) && isset($u['id'], $u['hoten'], $u['loai'])) ? $u : null;
}
function is_login() { return current_user() !== null; }
function login_user($u) {
    session_regenerate_id(true);
    unset($u['matkhau']);
    $_SESSION['user'] = $u;
}

// ----- Chuyển hướng quay lại an toàn (chỉ cho phép đường dẫn tương đối) -----
function safe_back($url, $default = 'index.php') {
    $url = (string)$url;
    if ($url !== '' && preg_match('/^[A-Za-z0-9_\-\.\?=&%\/]+$/', $url) && strpos($url, '//') === false && $url[0] !== '/') {
        return $url;
    }
    return $default;
}

// ----- Nhãn trạng thái -----
function trangthai_don($t) {
    $m = [0 => ['Chờ xác nhận', 'warning'], 1 => ['Đã xác nhận', 'info'], 2 => ['Đang chuẩn bị', 'primary'],
          3 => ['Hoàn thành', 'success'], 4 => ['Đã hủy', 'secondary']];
    return $m[(int)$t] ?? ['Không rõ', 'dark'];
}
function trangthai_thanh_toan($status) {
    $labels = [
        'unpaid' => ['Chưa thanh toán', 'warning'],
        'pending' => ['Chờ xác nhận thanh toán', 'info'],
        'paid' => ['Đã thanh toán', 'success'],
        'expired' => ['Hết hạn thanh toán', 'secondary'],
        'failed' => ['Thanh toán thất bại', 'danger'],
        'manual_review' => ['Cần kiểm tra thủ công', 'danger'],
    ];
    return $labels[(string)$status] ?? ['Không rõ', 'dark'];
}
function phuongthuc_thanh_toan($method) {
    return [
        'cod' => 'Tiền mặt khi nhận món',
        'bank_transfer' => 'Chuyển khoản ngân hàng',
        'vnpay' => 'VNPay (ngừng hỗ trợ)',
    ][(string)$method] ?? 'Không xác định';
}
function hinhthuc_don($h) { return [1 => 'Dùng tại quán', 2 => 'Mang đi', 3 => 'Giao hàng'][(int)$h] ?? '—'; }

// ----- Upload ảnh -----
// Trả về tên file đã lưu, null nếu không có file; ném RuntimeException nếu file không hợp lệ.
function upload_image_to_dir($field, $dirName) {
    if (empty($_FILES[$field]) || $_FILES[$field]['error'] === UPLOAD_ERR_NO_FILE) return null;
    $f = $_FILES[$field];
    if ($f['error'] !== UPLOAD_ERR_OK) throw new RuntimeException('Tải ảnh lên thất bại.');
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $ext = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    if (!$ext) throw new RuntimeException('Chỉ chấp nhận ảnh JPG, PNG hoặc WEBP.');
    $dir = ROOT . '/uploads/' . $dirName;
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    $name = bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $name)) throw new RuntimeException('Không lưu được ảnh.');
    return $name;
}
function upload_image($field) { return upload_image_to_dir($field, 'monan'); }
function img_url($name) { return $name ? '../uploads/monan/' . rawurlencode($name) : null; }
function img_url_dir($name, $dirName) { return $name ? '../uploads/' . $dirName . '/' . rawurlencode($name) : null; }
function monan_img_url($mon) {
    $uploadedImage = img_url($mon['hinhanh'] ?? null);
    if ($uploadedImage) return $uploadedImage;

    $images = [
        'Gỏi cuốn tôm thịt' => 'goicuon.jpg',
        'Chả giò hải sản' => 'chagio.jpg',
        'Súp cua trứng bạc' => 'suptrungcua.jpg',
        'Cơm chiên dương châu' => 'comchienduongchau.jpg',
        'Bò lúc lắc' => 'boluclac.jpg',
        'Cá hồi sốt chanh dây' => 'cahoichanhday.jpg',
        'Gà nướng mật ong' => 'ganuongmatong.jpg',
        'Lẩu Thái hải sản' => 'lauthai.jpg',
        'Sườn nướng BBQ' => 'suonnuongbbq.jpg',
        'Bò nướng lá lốt' => 'bonuonglalot.jpg',
        'Chè hạt sen long nhãn' => 'chehatsenlongnhan.jpg',
        'Bánh flan caramel' => 'banhflan.jpg',
        'Kem dừa' => 'kemdua.jpg',
        'Trà đào cam sả' => 'tradaocamsa.jpg',
        'Nước ép cam' => 'nuoccam.jpg',
        'Cà phê sữa đá' => 'caphesua.jpg',
    ];
    $image = $images[$mon['tenmon'] ?? ''] ?? null;
    return $image ? '../hinhanhmonan/' . rawurlencode($image) : null;
}

// ----- Cookie món đã xem (khách chưa đăng nhập) -----
function viewed_cookie() {
    $ids = array_filter(array_map('intval', explode(',', (string)($_COOKIE['gg_viewed'] ?? ''))));
    return array_values(array_unique($ids));
}
function viewed_cookie_add($id) {
    $ids = viewed_cookie();
    array_unshift($ids, (int)$id);
    $ids = array_slice(array_values(array_unique($ids)), 0, 20);
    setcookie('gg_viewed', implode(',', $ids), ['expires' => time() + 86400 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    $_COOKIE['gg_viewed'] = implode(',', $ids);
}
function viewed_cookie_clear() {
    setcookie('gg_viewed', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
    unset($_COOKIE['gg_viewed']);
}
