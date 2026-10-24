<?php
require __DIR__ . '/../core/bootstrap.php';

$action = $_REQUEST['action'] ?? 'home';
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

// ---------- Dữ liệu dùng chung ----------
$user      = current_user();
$chinhanhs = ChiNhanh::dangHoatDong();
$nhomon    = NhomMon::tatCa();
$ids = array_column($chinhanhs, 'id');
$cnId = (int)($_SESSION['chinhanh_id'] ?? ($_COOKIE['gg_cn'] ?? 0));
if (!in_array($cnId, array_map('intval', $ids), true)) $cnId = (int)($ids[0] ?? 0);
$_SESSION['chinhanh_id'] = $cnId;
$cb   = $cnId ? ChiNhanh::laySo($cnId) : null;
$soGio = GioHang::dem();
$here = 'index.php' . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '');

function view($name, $vars = []) {
    global $user, $chinhanhs, $nhomon, $cnId, $cb, $soGio, $here;
    extract($vars);
    include __DIR__ . '/' . $name . '.php';
    exit;
}
function can_login() { return is_login(); }
function need_login() {
    if (!is_login()) { flash('warning', 'Vui lòng đăng nhập để tiếp tục.'); redirect('index.php?action=dangnhap'); }
}

switch ($action) {

    // ===== THỰC ĐƠN =====
    case 'chonchinhanh':
        $id = (int)post('id');
        if (in_array($id, array_map('intval', $ids), true)) {
            $_SESSION['chinhanh_id'] = $id;
            setcookie('gg_cn', (string)$id, ['expires' => time() + 86400 * 30, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax']);
            if (!GioHang::trong()) flash('info', 'Đã đổi chi nhánh. Giỏ hàng sẽ được kiểm tra lại theo thực đơn và tồn kho chi nhánh này.');
        }
        redirect(safe_back(post('back')));

    case 'group':
        $gid = (int)($_GET['id'] ?? 0);
        $nhom = NhomMon::laySo($gid);
        if (!$nhom) { flash('danger', 'Nhóm món không tồn tại.'); redirect('index.php'); }
        view('group', ['title' => $nhom['tennhom'], 'heading' => $nhom['tennhom'], 'mons' => MonAn::layTheoChiNhanh($cnId, $gid)]);

    case 'timkiem':
        $q = trim((string)($_GET['q'] ?? ''));
        view('group', ['title' => 'Tìm kiếm', 'heading' => 'Kết quả cho "' . $q . '"', 'mons' => MonAn::layTheoChiNhanh($cnId, null, $q)]);

    case 'detail':
        $m = MonAn::layTheoId((int)($_GET['id'] ?? 0), $cnId);
        if (!$m || !$m['trangthai']) { flash('danger', 'Món ăn không tồn tại.'); redirect('index.php'); }
        MonAn::tangLuotXem($m['id']);
        if ($user) GoiY::ghiNhanXem($user['id'], $m['id']); else viewed_cookie_add($m['id']);
        $cungNhom = array_values(array_filter(MonAn::layTheoChiNhanh($cnId, $m['nhom_id']), fn($x) => (int)$x['id'] !== (int)$m['id']));
        view('detail', ['title' => $m['tenmon'], 'm' => $m, 'cungNhom' => array_slice($cungNhom, 0, 4)]);

    // ===== GIỎ HÀNG =====
    case 'chovaogio':
        $m = MonAn::layTheoId((int)post('id'), $cnId);
        $sl = max(1, min(GioHang::MAX_MOI_MON, (int)post('soluong', '1')));
        if (!$m || !$m['trangthai'] || !$m['conphucvu']) {
            flash('danger', 'Món này hiện không phục vụ tại chi nhánh đã chọn.');
        } elseif ((int)$m['soluongton'] <= 0) {
            flash('danger', 'Món "' . $m['tenmon'] . '" tạm hết.');
        } else {
            $tong = min((int)$m['soluongton'], GioHang::soLuong($m['id']) + $sl);
            GioHang::dat($m['id'], $tong);
            flash('success', 'Đã thêm "' . $m['tenmon'] . '" vào giỏ hàng.');
        }
        redirect(safe_back(post('back')));

    case 'giohang':
        $dong = GioHang::dong($cnId);
        view('cart', ['title' => 'Giỏ hàng', 'dong' => $dong, 'tam' => GioHang::tamTinh($dong)]);

    case 'capnhatgio':
        $sls = $_POST['soluong'] ?? [];
        if (is_array($sls)) {
            foreach ($sls as $id => $sl) {
                $m = MonAn::layTheoId((int)$id, $cnId);
                $sl = (int)$sl;
                if ($sl > 0 && $m && $sl > (int)$m['soluongton']) {
                    $sl = max(0, (int)$m['soluongton']);
                    flash('warning', 'Món "' . $m['tenmon'] . '" chỉ còn ' . $sl . ' phần, đã điều chỉnh số lượng.');
                }
                GioHang::dat((int)$id, $sl);
            }
        }
        flash('success', 'Đã cập nhật giỏ hàng.');
        redirect('index.php?action=giohang');

    case 'xoamon':
        GioHang::xoa((int)post('id'));
        redirect('index.php?action=giohang');

    // ===== THANH TOÁN =====
    case 'thanhtoan':
        $dong = GioHang::dong($cnId);
        if (!$dong) { flash('warning', 'Giỏ hàng đang trống.'); redirect('index.php'); }
        $old = $_SESSION['old_checkout'] ?? [];
        unset($_SESSION['old_checkout']);
        $f = $old + [
            'hoten' => $user['hoten'] ?? '', 'sdt' => $user['sodienthoai'] ?? '',
            'diachi' => $user ? NguoiDung::diaChiMacDinh($user['id']) : '', 'hinhthuc' => 2, 'ghichu' => '',
        ];
        view('checkout', ['title' => 'Đặt món', 'dong' => $dong, 'tam' => GioHang::tamTinh($dong), 'f' => $f]);

    case 'xlthanhtoan':
        $dong = GioHang::dong($cnId);
        if (!$dong) redirect('index.php');
        $f = ['hoten' => post('hoten'), 'sdt' => post('sdt'), 'diachi' => post('diachi'), 'hinhthuc' => (int)post('hinhthuc', '2'), 'ghichu' => post('ghichu')];
        $err = null;
        if ($f['hoten'] === '') $err = 'Vui lòng nhập họ tên.';
        elseif (!preg_match('/^0\d{9}$/', $f['sdt'])) $err = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.';
        elseif (!in_array($f['hinhthuc'], [1, 2, 3], true)) $err = 'Hình thức nhận món không hợp lệ.';
        elseif ($f['hinhthuc'] === 3 && $f['diachi'] === '') $err = 'Vui lòng nhập địa chỉ giao hàng.';
        if ($err) { flash('danger', $err); $_SESSION['old_checkout'] = $f; redirect('index.php?action=thanhtoan'); }

        if ($user) {
            $uid = (int)$user['id'];
        } else {
            $ex = NguoiDung::timTheoSdt($f['sdt']);
            if ($ex && (int)$ex['dadangky'] === 1) {
                flash('warning', 'Số điện thoại này đã có tài khoản. Vui lòng đăng nhập để đặt món.');
                $_SESSION['old_checkout'] = $f;
                redirect('index.php?action=dangnhap');
            }
            $uid = $ex ? (int)$ex['id'] : NguoiDung::taoKhachVangLai($f['hoten'], $f['sdt']);
        }
        try {
            $items = [];
            foreach ($dong as $d) $items[(int)$d['id']] = (int)$d['soluong'];
            $diachi = $f['hinhthuc'] === 3 ? $f['diachi'] : null;
            $did = DonHang::tao($uid, $cnId, $f['hinhthuc'], $f['hoten'], $f['sdt'], $diachi, $f['ghichu'], $items);
            if ($diachi) NguoiDung::luuDiaChi($uid, $diachi);
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
            $_SESSION['old_checkout'] = $f;
            redirect('index.php?action=giohang');
        }
        GioHang::xoaTatCa();
        $_SESSION['don_vua_dat'] = $did;
        redirect('index.php?action=hoantat&id=' . $did);

    case 'hoantat':
        $did = (int)($_GET['id'] ?? 0);
        $d = DonHang::laySo($did);
        $owner = $d && $user && (int)$d['nguoidung_id'] === (int)$user['id'];
        if (!$d || !($owner || ($_SESSION['don_vua_dat'] ?? 0) === $did)) { flash('danger', 'Không tìm thấy đơn hàng.'); redirect('index.php'); }
        view('success', ['title' => 'Đặt món thành công', 'd' => $d, 'ct' => DonHang::chiTiet($did)]);

    // ===== TÀI KHOẢN =====
    case 'dangnhap':
        if ($user) redirect('index.php');
        view('login', ['title' => 'Đăng nhập']);

    case 'xldangnhap':
        $khoa = $_SESSION['login_lock'] ?? 0;
        if ($khoa > time()) { flash('danger', 'Bạn nhập sai quá nhiều lần. Vui lòng thử lại sau vài phút.'); redirect('index.php?action=dangnhap'); }
        $u = NguoiDung::dangNhap(post('dinhdanh'), (string)($_POST['matkhau'] ?? ''));
        if ($u === false) { flash('danger', 'Tài khoản đã bị khóa.'); redirect('index.php?action=dangnhap'); }
        if (!$u) {
            $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
            if ($_SESSION['login_fail'] >= 5) { $_SESSION['login_lock'] = time() + 300; $_SESSION['login_fail'] = 0; }
            flash('danger', 'Sai thông tin đăng nhập.');
            redirect('index.php?action=dangnhap');
        }
        unset($_SESSION['login_fail'], $_SESSION['login_lock']);
        $gio = $_SESSION['giohang'] ?? null;     // giữ giỏ hàng khi đăng nhập
        login_user($u);
        if ($gio) $_SESSION['giohang'] = $gio;
        flash('success', 'Xin chào ' . $u['hoten'] . '!');
        redirect(!empty($_SESSION['old_checkout']) ? 'index.php?action=thanhtoan' : 'index.php');

    case 'dangky':
        if ($user) redirect('index.php');
        view('register', ['title' => 'Đăng ký', 'f' => $_SESSION['old_register'] ?? []]);

    case 'xldangky':
        $f = ['hoten' => post('hoten'), 'sdt' => post('sdt'), 'email' => post('email')];
        $mk = (string)($_POST['matkhau'] ?? ''); $mk2 = (string)($_POST['matkhau2'] ?? '');
        $err = null;
        if ($f['hoten'] === '') $err = 'Vui lòng nhập họ tên.';
        elseif (!preg_match('/^0\d{9}$/', $f['sdt'])) $err = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.';
        elseif ($f['email'] !== '' && !filter_var($f['email'], FILTER_VALIDATE_EMAIL)) $err = 'Email không hợp lệ.';
        elseif (strlen($mk) < 6) $err = 'Mật khẩu tối thiểu 6 ký tự.';
        elseif ($mk !== $mk2) $err = 'Mật khẩu nhập lại không khớp.';
        if (!$err) {
            try {
                $uid = NguoiDung::dangKy($f['hoten'], $f['sdt'], $f['email'], $mk);
                unset($_SESSION['old_register']);
                $gio = $_SESSION['giohang'] ?? null;
                login_user(NguoiDung::laySo($uid));
                if ($gio) $_SESSION['giohang'] = $gio;
                flash('success', 'Đăng ký thành công. Chào mừng bạn đến với GoldenGate!');
                redirect('index.php');
            } catch (RuntimeException $ex) { $err = $ex->getMessage(); }
        }
        $_SESSION['old_register'] = $f;
        flash('danger', $err);
        redirect('index.php?action=dangky');

    case 'dangxuat':
        $_SESSION = [];
        session_destroy();
        session_start();
        flash('info', 'Bạn đã đăng xuất.');
        redirect('index.php');

    // ===== ĐƠN HÀNG CỦA TÔI =====
    case 'donhang':
        need_login();
        view('orders', ['title' => 'Đơn hàng của tôi', 'dons' => DonHang::cuaNguoiDung($user['id'])]);

    case 'chitietdon':
        need_login();
        $d = DonHang::laySo((int)($_GET['id'] ?? 0));
        if (!$d || (int)$d['nguoidung_id'] !== (int)$user['id']) { flash('danger', 'Không tìm thấy đơn hàng.'); redirect('index.php?action=donhang'); }
        view('orderdetail', ['title' => 'Đơn ' . maDon($d['id']), 'd' => $d, 'ct' => DonHang::chiTiet($d['id'])]);

    case 'datlai':
        need_login();
        try {
            [$them, $bo] = GoiY::datLai($user['id'], (int)post('id'), $cnId);
            if ($them === 0) flash('warning', 'Các món trong đơn này hiện không có ở chi nhánh đang chọn.');
            else flash('success', 'Đã thêm ' . $them . ' món vào giỏ' . ($bo ? " ($bo món hết hoặc không phục vụ đã được bỏ qua)" : '') . '.');
        } catch (RuntimeException $ex) { flash('danger', $ex->getMessage()); }
        redirect('index.php?action=giohang');

    case 'huydon':
        need_login();
        try { DonHang::khachHuy((int)post('id'), $user['id']); flash('success', 'Đã hủy đơn hàng.'); }
        catch (RuntimeException $ex) { flash('danger', $ex->getMessage()); }
        redirect('index.php?action=donhang');

    case 'tracuu':
        $d = null; $ct = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ma = (int)preg_replace('/\D/', '', post('ma'));
            $d = DonHang::traCuu($ma, post('sdt'));
            if ($d) $ct = DonHang::chiTiet($d['id']); else flash('warning', 'Không tìm thấy đơn phù hợp.');
        }
        view('tracking', ['title' => 'Tra cứu đơn hàng', 'd' => $d, 'ct' => $ct]);

    // ===== TRANG CHỦ =====
    case 'home':
    default:
        $goiy = GoiY::cho($user['id'] ?? null, $cnId, viewed_cookie(), 4);
        $menu = [];
        foreach (MonAn::layTheoChiNhanh($cnId) as $m) $menu[$m['tennhom']][] = $m;
        $donCu = $user ? GoiY::donGanNhat($user['id']) : null;
        view('main', ['title' => 'Trang chủ', 'goiy' => $goiy, 'menu' => $menu, 'donCu' => $donCu]);
}
