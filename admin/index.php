<?php
require __DIR__ . '/../core/bootstrap.php';

$action = $_REQUEST['action'] ?? 'dashboard';
if ($_SERVER['REQUEST_METHOD'] === 'POST') csrf_verify();

function aview($name, $vars = []) {
    global $user, $isAdmin, $myCn, $action;
    extract($vars);
    include __DIR__ . '/' . $name . '.php';
    exit;
}
function only_admin() {
    global $isAdmin;
    if (!$isAdmin) { flash('danger', 'Chức năng này chỉ dành cho quản trị tổng.'); redirect('index.php'); }
}

// ---------- Đăng nhập quản trị ----------
if ($action === 'login' || $action === 'xldangnhap') {
    if ($action === 'xldangnhap') {
        $khoa = $_SESSION['login_lock'] ?? 0;
        if ($khoa > time()) { flash('danger', 'Nhập sai quá nhiều lần. Vui lòng thử lại sau vài phút.'); redirect('index.php?action=login'); }
        $u = NguoiDung::dangNhap(post('dinhdanh'), (string)($_POST['matkhau'] ?? ''));
        if ($u && (int)$u['loai'] <= 2) {
            unset($_SESSION['login_fail'], $_SESSION['login_lock']);
            login_user($u);
            redirect('index.php');
        }
        $_SESSION['login_fail'] = ($_SESSION['login_fail'] ?? 0) + 1;
        if ($_SESSION['login_fail'] >= 5) { $_SESSION['login_lock'] = time() + 300; $_SESSION['login_fail'] = 0; }
        flash('danger', $u === false ? 'Tài khoản đã bị khóa.' : 'Sai thông tin hoặc không có quyền quản trị.');
        redirect('index.php?action=login');
    }
    include __DIR__ . '/login.php';
    exit;
}
if ($action === 'dangxuat') { $_SESSION = []; session_destroy(); redirect('index.php?action=login'); }

$user = current_user();
if (!$user || (int)$user['loai'] > 2) redirect('index.php?action=login');
// Lấy lại thông tin mới nhất (phòng khi bị khóa hoặc đổi chi nhánh)
$fresh = NguoiDung::laySo($user['id']);
if (!$fresh || !$fresh['trangthai'] || (int)$fresh['loai'] > 2) { $_SESSION = []; redirect('index.php?action=login'); }
$user = $_SESSION['user'] = array_diff_key($fresh, ['matkhau' => 1]);
$isAdmin = (int)$user['loai'] === 1;
$myCn = $isAdmin ? null : (int)$user['chinhanh_id'];   // quản lý chỉ thấy chi nhánh của mình

switch ($action) {

    case 'dashboard':
    default:
        aview('dashboard', [
            'tq'     => ThongKe::tongQuan($myCn),
            'cnRev'  => $isAdmin ? ThongKe::doanhThuTheoChiNhanh(30) : [],
            'top'    => ThongKe::topMon($myCn, 30, 5),
            'saphet' => ThongKe::sapHetHang($myCn),
            'dons'   => DonHang::danhSach($myCn, 0, 8),
        ]);

    // ===== CHI NHÁNH =====
    case 'chinhanh':
        only_admin();
        aview('chinhanh', ['ds' => ChiNhanh::tatCa(), 'edit' => !empty($_GET['edit']) ? ChiNhanh::laySo((int)$_GET['edit']) : null]);
    case 'luuchinhanh':
        only_admin();
        $id = (int)post('id'); $ten = post('ten'); $dc = post('diachi');
        if ($ten === '' || $dc === '') { flash('danger', 'Tên và địa chỉ chi nhánh là bắt buộc.'); redirect('index.php?action=chinhanh'); }
        $mo = post('mo', '10:00'); $dong = post('dong', '22:00');
        if ($id) ChiNhanh::sua($id, $ten, $dc, post('sdt'), $mo, $dong); else ChiNhanh::them($ten, $dc, post('sdt'), $mo, $dong);
        flash('success', 'Đã lưu chi nhánh.');
        redirect('index.php?action=chinhanh');
    case 'doitrangthaicn':
        only_admin(); ChiNhanh::doiTrangThai((int)post('id')); flash('success', 'Đã đổi trạng thái chi nhánh.'); redirect('index.php?action=chinhanh');

    // ===== NHÓM MÓN =====
    case 'nhomon':
        only_admin();
        aview('nhomon', ['ds' => NhomMon::tatCa(), 'edit' => !empty($_GET['edit']) ? NhomMon::laySo((int)$_GET['edit']) : null]);
    case 'luunhom':
        only_admin();
        $id = (int)post('id'); $ten = post('ten');
        if ($ten === '') { flash('danger', 'Tên nhóm là bắt buộc.'); redirect('index.php?action=nhomon'); }
        if ($id) NhomMon::sua($id, $ten); else NhomMon::them($ten);
        flash('success', 'Đã lưu nhóm món.');
        redirect('index.php?action=nhomon');
    case 'xoanhom':
        only_admin();
        if (NhomMon::xoa((int)post('id'))) flash('success', 'Đã xóa nhóm món.'); else flash('danger', 'Không thể xóa: nhóm vẫn còn món ăn.');
        redirect('index.php?action=nhomon');

    // ===== MÓN ĂN =====
    case 'monan':
        only_admin();
        aview('monan', ['ds' => MonAn::tatCa()]);
    case 'monan_form':
        only_admin();
        aview('monan_form', ['m' => !empty($_GET['id']) ? MonAn::laySo((int)$_GET['id']) : null, 'nhoms' => NhomMon::tatCa()]);
    case 'luumon':
        only_admin();
        $id = (int)post('id'); $ten = post('ten'); $gia = (int)post('gia'); $nhom = (int)post('nhom');
        if ($ten === '' || $gia <= 0 || !NhomMon::laySo($nhom)) {
            flash('danger', 'Vui lòng nhập tên món, giá lớn hơn 0 và chọn nhóm món.');
            redirect('index.php?action=monan_form' . ($id ? '&id=' . $id : ''));
        }
        try { $hinh = upload_image('hinh'); }
        catch (RuntimeException $e) { flash('danger', $e->getMessage()); redirect('index.php?action=monan_form' . ($id ? '&id=' . $id : '')); }
        if ($id) MonAn::sua($id, $ten, post('mota'), $gia, $nhom, $hinh);
        else MonAn::them($ten, post('mota'), $gia, $nhom, $hinh);
        flash('success', 'Đã lưu món ăn. Nhớ nhập tồn kho cho từng chi nhánh ở mục "Tồn kho".');
        redirect('index.php?action=monan');
    case 'doitrangthaimon':
        only_admin(); MonAn::doiTrangThai((int)post('id')); flash('success', 'Đã đổi trạng thái món.'); redirect('index.php?action=monan');

    // ===== TỒN KHO THEO CHI NHÁNH =====
    case 'tonkho':
        $cn = $isAdmin ? (int)($_GET['cn'] ?? 0) : $myCn;
        $cns = $isAdmin ? ChiNhanh::tatCa() : [ChiNhanh::laySo($myCn)];
        if (!$cn && $cns) $cn = (int)$cns[0]['id'];
        aview('tonkho', ['cn' => $cn, 'cns' => $cns, 'ds' => $cn ? MonAn::tonTheoChiNhanh($cn) : []]);
    case 'luutonkho':
        $cn = $isAdmin ? (int)post('cn') : $myCn;
        if (!ChiNhanh::laySo($cn)) { flash('danger', 'Chi nhánh không hợp lệ.'); redirect('index.php?action=tonkho'); }
        $ton = $_POST['ton'] ?? []; $pv = $_POST['phucvu'] ?? [];
        if (is_array($ton)) foreach ($ton as $mid => $sl) MonAn::capNhatTon($cn, (int)$mid, (int)$sl, isset($pv[$mid]));
        flash('success', 'Đã cập nhật tồn kho.');
        redirect('index.php?action=tonkho&cn=' . $cn);

    // ===== ĐƠN HÀNG =====
    case 'donhang':
        $cn = $isAdmin ? (int)($_GET['cn'] ?? 0) : $myCn;
        $tt = $_GET['tt'] ?? '';
        aview('donhang', ['ds' => DonHang::danhSach($cn ?: null, $tt === '' ? null : (int)$tt, 200), 'cn' => $cn, 'tt' => $tt, 'cns' => $isAdmin ? ChiNhanh::tatCa() : []]);
    case 'chitietdon':
        $d = DonHang::laySo((int)($_GET['id'] ?? 0));
        if (!$d || (!$isAdmin && (int)$d['chinhanh_id'] !== $myCn)) { flash('danger', 'Không tìm thấy đơn hàng.'); redirect('index.php?action=donhang'); }
        aview('donhang_ct', ['d' => $d, 'ct' => DonHang::chiTiet($d['id'])]);
    case 'capnhatdon':
        try { DonHang::capNhatTrangThai((int)post('id'), (int)post('trangthai'), $myCn); flash('success', 'Đã cập nhật trạng thái đơn.'); }
        catch (RuntimeException $e) { flash('danger', $e->getMessage()); }
        redirect('index.php?action=chitietdon&id=' . (int)post('id'));

    // ===== NGƯỜI DÙNG =====
    case 'nguoidung':
        only_admin();
        aview('nguoidung', ['ds' => NguoiDung::danhSach(), 'cns' => ChiNhanh::tatCa()]);
    case 'luunhansu':
        only_admin();
        $loai = (int)post('loai'); $cn = (int)post('chinhanh');
        $mk = (string)($_POST['matkhau'] ?? '');
        if (post('hoten') === '' || !preg_match('/^0\d{9}$/', post('sdt')) || strlen($mk) < 6 || !in_array($loai, [1, 2], true) || ($loai === 2 && !ChiNhanh::laySo($cn))) {
            flash('danger', 'Thông tin chưa hợp lệ (họ tên, SĐT 10 số, mật khẩu ≥ 6 ký tự, quản lý phải chọn chi nhánh).');
            redirect('index.php?action=nguoidung');
        }
        try { NguoiDung::taoNhanSu(post('hoten'), post('sdt'), post('email'), $mk, $loai, $loai === 2 ? $cn : null); flash('success', 'Đã tạo tài khoản.'); }
        catch (RuntimeException $e) { flash('danger', $e->getMessage()); }
        redirect('index.php?action=nguoidung');
    case 'khoanguoidung':
        only_admin();
        $id = (int)post('id');
        if ($id === (int)$user['id']) flash('danger', 'Không thể tự khóa tài khoản của mình.');
        else { NguoiDung::doiTrangThai($id); flash('success', 'Đã đổi trạng thái tài khoản.'); }
        redirect('index.php?action=nguoidung');

    // ===== BÁO CÁO =====
    case 'baocao':
        $days = in_array((int)($_GET['days'] ?? 30), [7, 30, 90], true) ? (int)$_GET['days'] : 30;
        aview('baocao', [
            'days' => $days,
            'cnRev' => $isAdmin ? ThongKe::doanhThuTheoChiNhanh($days) : [],
            'ngay' => ThongKe::doanhThuTheoNgay($myCn, $days),
            'top' => ThongKe::topMon($myCn, $days, 10),
        ]);
}
