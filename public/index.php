<?php
require __DIR__ . '/../core/bootstrap.php';

$action = $_REQUEST['action'] ?? 'home';
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

// ---------- Dữ liệu dùng chung ----------
$user      = current_user();
if ($user) {
    $guestViews = viewed_cookie();
    if ($guestViews) {
        GoiY::nhapLuotXemCookie($user['id'], $guestViews);
        viewed_cookie_clear();
    }
}
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
        view('detail', [
            'title' => $m['tenmon'],
            'm' => $m,
            'cungNhom' => array_slice($cungNhom, 0, 4),
            'reviews' => DanhGia::theoMon($m['id']),
            'reviewSummary' => DanhGia::tongQuanMon($m['id']),
        ]);

    case 'sukien':
        view('events', [
            'title' => 'Sự kiện và ưu đãi',
            'events' => SuKien::dangDienRa($cnId),
        ]);

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
            'hoten' => $user['hoten'] ?? '', 'sdt' => $user['sodienthoai'] ?? '', 'phuongthuc_tt' => 'cod',
            'diachi' => $user ? NguoiDung::diaChiMacDinh($user['id']) : '', 'hinhthuc' => 2, 'ghichu' => '', 'voucher' => '',
        ];
        $tam = GioHang::tamTinh($dong);
        $voucherPreview = ['voucher' => null, 'giamgia' => 0];
        $voucherError = null;
        try { $voucherPreview = Voucher::tinhGiam($f['voucher'], $cnId, $tam); }
        catch (RuntimeException $ex) { $voucherError = $ex->getMessage(); }
        view('checkout', [
            'title' => 'Đặt món', 'dong' => $dong, 'tam' => $tam, 'f' => $f,
            'voucherPreview' => $voucherPreview, 'voucherError' => $voucherError,
        ]);

    case 'xlthanhtoan':
        $dong = GioHang::dong($cnId);
        if (!$dong) redirect('index.php');
        $f = [
            'hoten' => post('hoten'), 'sdt' => post('sdt'), 'diachi' => post('diachi'),
            'hinhthuc' => (int)post('hinhthuc', '2'), 'ghichu' => post('ghichu'),
            'voucher' => strtoupper(post('voucher')), 'phuongthuc_tt' => post('phuongthuc_tt', 'cod'),
        ];
        if (isset($_POST['kiemtra_voucher'])) {
            $_SESSION['old_checkout'] = $f;
            try {
                $preview = Voucher::tinhGiam($f['voucher'], $cnId, GioHang::tamTinh($dong));
                flash('success', $preview['voucher'] ? 'Voucher hợp lệ, giảm ' . money($preview['giamgia']) . '.' : 'Đã bỏ mã voucher.');
            } catch (RuntimeException $ex) {
                flash('danger', $ex->getMessage());
            }
            redirect('index.php?action=thanhtoan');
        }
        $err = null;
        if ($f['hoten'] === '') $err = 'Vui lòng nhập họ tên.';
        elseif (!preg_match('/^0\d{9}$/', $f['sdt'])) $err = 'Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.';
        elseif (!in_array($f['hinhthuc'], [1, 2, 3], true)) $err = 'Hình thức nhận món không hợp lệ.';
        elseif (!in_array($f['phuongthuc_tt'], ['cod', 'bank_transfer'], true)) $err = 'Phương thức thanh toán không hợp lệ.';
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
        $did = null;
        try {
            $items = [];
            foreach ($dong as $d) $items[(int)$d['id']] = (int)$d['soluong'];
            $diachi = $f['hinhthuc'] === 3 ? $f['diachi'] : null;
            $did = DonHang::tao(
                $uid, $cnId, $f['hinhthuc'], $f['hoten'], $f['sdt'], $diachi,
                $f['ghichu'], $items, $f['voucher'], $f['phuongthuc_tt']
            );
            if ($diachi) NguoiDung::luuDiaChi($uid, $diachi);
            if ($f['phuongthuc_tt'] === 'bank_transfer') {
                DonHang::batDauChuyenKhoan($did, $uid);
                GioHang::xoaTatCa();
                $_SESSION['don_vua_dat'] = $did;
                redirect('index.php?action=chuyenkhoan&id=' . $did);
            }
        } catch (RuntimeException $ex) {
            if ($did) {
                try { DonHang::capNhatTrangThai($did, 4, null, $uid, 0); }
                catch (RuntimeException $cancelError) { error_log('Unable to release failed checkout order ' . (int)$did . ': ' . $cancelError->getMessage()); }
            }
            flash('danger', $ex->getMessage());
            $_SESSION['old_checkout'] = $f;
            redirect('index.php?action=thanhtoan');
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

    case 'chuyenkhoan':
        $did = (int)($_GET['id'] ?? 0);
        $d = DonHang::laySo($did);
        $owner = $d && $user && (int)$d['nguoidung_id'] === (int)$user['id'];
        $recentGuestOrder = $d && (int)($_SESSION['don_vua_dat'] ?? 0) === (int)$d['id'];
        if (!$d || !($owner || $recentGuestOrder) || $d['phuongthuc_tt'] !== 'bank_transfer') {
            flash('danger', 'Không tìm thấy đơn chuyển khoản.');
            redirect('index.php');
        }
        if ($d['trangthai_tt'] === 'pending' && !empty($d['thanhtoan_het_han'])
            && strtotime($d['thanhtoan_het_han']) <= time()) {
            DonHang::hetHanChuyenKhoan($did);
            $d = DonHang::laySo($did);
        }
        view('bank_transfer', ['title' => 'Thanh toán chuyển khoản', 'd' => $d]);

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

    case 'hoso':
        need_login();
        view('account', [
            'title' => 'Tài khoản của tôi',
            'addresses' => NguoiDung::danhSachDiaChi($user['id']),
        ]);

    case 'luuhoso':
        need_login();
        try {
            NguoiDung::capNhatHoSo($user['id'], post('hoten'), post('sdt'), post('email'));
            login_user(NguoiDung::laySo($user['id']));
            flash('success', 'Đã cập nhật thông tin tài khoản.');
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('index.php?action=hoso');

    case 'themdiachi':
        need_login();
        try {
            NguoiDung::themDiaChi($user['id'], post('diachi'));
            flash('success', 'Đã thêm địa chỉ giao hàng.');
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('index.php?action=hoso');

    case 'datdiachimacdinh':
        need_login();
        try {
            NguoiDung::datDiaChiMacDinh($user['id'], (int)post('id'));
            flash('success', 'Đã cập nhật địa chỉ mặc định.');
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('index.php?action=hoso');

    case 'xoadiachi':
        need_login();
        try {
            NguoiDung::xoaDiaChi($user['id'], (int)post('id'));
            flash('success', 'Đã xóa địa chỉ giao hàng.');
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('index.php?action=hoso');

    // ===== ĐƠN HÀNG CỦA TÔI =====
    case 'donhang':
        need_login();
        view('orders', ['title' => 'Đơn hàng của tôi', 'dons' => DonHang::cuaNguoiDung($user['id'])]);

    case 'chitietdon':
        $d = DonHang::laySo((int)($_GET['id'] ?? 0));
        $guestOrder = $d && !$user && (int)($_SESSION['don_vua_dat'] ?? 0) === (int)$d['id'];
        if (!$user && !$guestOrder) need_login();
        if (!$d || (!$guestOrder && (int)$d['nguoidung_id'] !== (int)$user['id'])) { flash('danger', 'Không tìm thấy đơn hàng.'); redirect('index.php?action=donhang'); }
        if ($d['phuongthuc_tt'] === 'bank_transfer' && $d['trangthai_tt'] === 'pending'
            && !empty($d['thanhtoan_het_han']) && strtotime($d['thanhtoan_het_han']) <= time()) {
            DonHang::hetHanChuyenKhoan((int)$d['id']);
            $d = DonHang::laySo((int)$d['id']);
        }
        $reviewsByFood = [];
        if ($user) {
            foreach (DanhGia::cuaDon($d['id'], $user['id']) as $review) {
                $reviewsByFood[(int)$review['monan_id']] = $review;
            }
        }
        view('orderdetail', [
            'title' => 'Đơn ' . maDon($d['id']),
            'd' => $d,
            'ct' => DonHang::chiTiet($d['id']),
            'reviewsByFood' => $reviewsByFood,
            'history' => DonHang::lichSu($d['id']),
        ]);

    case 'danhgia':
        need_login();
        try {
            DanhGia::tao(
                $user['id'],
                (int)post('donhang_id'),
                (int)post('monan_id'),
                (int)post('so_sao'),
                post('noi_dung')
            );
            flash('success', 'Cảm ơn bạn đã gửi đánh giá.');
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('index.php?action=chitietdon&id=' . (int)post('donhang_id'));

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

    // ===== ĐẶT BÀN =====
    case 'datban':
        $old = $_SESSION['old_booking'] ?? [];
        unset($_SESSION['old_booking']);
        $bookingForm = $old + [
            'hoten' => $user['hoten'] ?? '', 'sdt' => $user['sodienthoai'] ?? '',
            'chinhanh_id' => $cnId, 'thoi_gian' => '', 'so_khach' => 2, 'ghichu' => '',
        ];
        view('booking', [
            'title' => 'Đặt bàn', 'bookingForm' => $bookingForm,
            'bookings' => $user ? DatBan::cuaNguoiDung($user['id']) : [],
        ]);

    case 'xldatban':
        $bookingForm = [
            'hoten' => post('hoten'), 'sdt' => post('sdt'),
            'chinhanh_id' => (int)post('chinhanh_id'), 'thoi_gian' => post('thoi_gian'),
            'so_khach' => (int)post('so_khach'), 'ghichu' => post('ghichu'),
        ];
        try {
            DatBan::tao($bookingForm, $user['id'] ?? null);
            flash('success', 'Đã gửi yêu cầu đặt bàn. Nhà hàng sẽ liên hệ xác nhận với bạn.');
        } catch (RuntimeException $ex) {
            $_SESSION['old_booking'] = $bookingForm;
            flash('danger', $ex->getMessage());
            redirect('index.php?action=datban');
        }
        redirect('index.php?action=datban');

    case 'huydatban':
        need_login();
        try {
            DatBan::khachHuy((int)post('id'), $user['id']);
            flash('success', 'Đã hủy yêu cầu đặt bàn.');
        } catch (RuntimeException $ex) {
            flash('danger', $ex->getMessage());
        }
        redirect('index.php?action=datban');

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
        $slides = Slider::tatCa();
        if (!$slides) {
            $slides = [
                [
                    'id' => 0,
                    'tieu_de' => 'Hương vị Việt - Chuẩn nhà hàng',
                    'mota' => 'Bạn đang xem thực đơn của <strong>' . e($cb['tenchinhanh'] ?? '') . '</strong>. Có thể đổi chi nhánh ở góc trên bên phải.',
                    'hinh_anh' => 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=1600&q=80',
                    'nut_text' => 'Xem thực đơn',
                    'nut_link' => '#menu',
                    'trangthai' => 1,
                    'vitri' => 0,
                ],
            ];
        }
        view('main', ['title' => 'Trang chủ', 'goiy' => $goiy, 'menu' => $menu, 'donCu' => $donCu, 'slides' => $slides]);
}
