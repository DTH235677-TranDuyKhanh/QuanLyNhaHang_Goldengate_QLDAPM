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
// ---------- Xử lý đăng nhập ----------
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
        try {
            if ($id) ChiNhanh::sua($id, $ten, $dc, post('sdt'), $mo, $dong, post('suc_chua'), post('thoi_luong'));
            else ChiNhanh::them($ten, $dc, post('sdt'), $mo, $dong, post('suc_chua'), post('thoi_luong'));
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
            redirect('index.php?action=chinhanh' . ($id ? '&edit=' . $id : ''));
        }
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

    // ===== SLIDER =====
    case 'slider':
        only_admin();
        aview('slider', ['ds' => Slider::tatCaAdmin(), 'edit' => !empty($_GET['edit']) ? Slider::laySo((int)$_GET['edit']) : null]);
    case 'luuslider':
        only_admin();
        $id = (int)post('id');
        $tieuDe = post('tieu_de');
        $mota = post('mota');
        $nutText = post('nut_text');
        $nutLink = post('nut_link', '#');
        $vitri = (int)post('vitri', 0);
        if ($tieuDe === '') { flash('danger', 'Tiêu đề slide là bắt buộc.'); redirect('index.php?action=slider'); }
        try {
            $hinh = upload_image_to_dir('hinh_anh', 'slider');
            if (!$hinh && $id) {
                $cur = Slider::laySo($id);
                $hinh = $cur['hinh_anh'] ?? null;
            }
            if (!$hinh) {
                flash('danger', 'Vui lòng chọn ảnh cho slide.');
                redirect('index.php?action=slider' . ($id ? '&edit=' . $id : ''));
            }
            if ($id) Slider::sua($id, $tieuDe, $mota, $hinh, $nutText, $nutLink, $vitri, (int)post('trangthai', 0));
            else Slider::them($tieuDe, $mota, $hinh, $nutText, $nutLink, $vitri, (int)post('trangthai', 0));
            flash('success', 'Đã lưu slide.');
            redirect('index.php?action=slider');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
            redirect('index.php?action=slider' . ($id ? '&edit=' . $id : ''));
        }
    case 'doitrangthaislider':
        only_admin();
        Slider::doiTrangThai((int)post('id'));
        flash('success', 'Đã đổi trạng thái slide.');
        redirect('index.php?action=slider');
    case 'xoaslider':
        only_admin();
        Slider::xoa((int)post('id'));
        flash('success', 'Đã xóa slide.');
        redirect('index.php?action=slider');

    // ===== SỰ KIỆN VÀ ƯU ĐÃI =====
    case 'sukien':
        only_admin();
        aview('sukien', [
            'ds' => SuKien::tatCaAdmin(),
            'edit' => !empty($_GET['edit']) ? SuKien::laySo((int)$_GET['edit']) : null,
            'cns' => ChiNhanh::tatCa(),
            'vouchers' => Voucher::tatCa(),
        ]);
    case 'luusukien':
        only_admin();
        $id = (int)post('id');
        try {
            $image = upload_image_to_dir('hinh_anh', 'sukien');
            SuKien::luu($_POST, $image);
            flash('success', 'Đã lưu sự kiện.');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('index.php?action=sukien' . ($id ? '&edit=' . $id : ''));
    case 'doitrangthaisukien':
        only_admin();
        if (SuKien::doiTrangThai((int)post('id'))) flash('success', 'Đã đổi trạng thái sự kiện.');
        else flash('danger', 'Không tìm thấy sự kiện.');
        redirect('index.php?action=sukien');

    // ===== PHẢN HỒI VÀ ĐÁNH GIÁ =====
    case 'danhgia':
        only_admin();
        aview('danhgia', ['ds' => DanhGia::tatCaAdmin()]);
    case 'doitrangthaidanhgia':
        only_admin();
        if (DanhGia::doiTrangThai((int)post('id'))) flash('success', 'Đã cập nhật trạng thái đánh giá.');
        else flash('danger', 'Không tìm thấy đánh giá.');
        redirect('index.php?action=danhgia');
    case 'phanhoidanhgia':
        only_admin();
        try {
            DanhGia::phanHoi((int)post('id'), post('phan_hoi'), (int)$user['id']);
            flash('success', 'Đã lưu phản hồi cho khách hàng.');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('index.php?action=danhgia');

    // ===== TỒN KHO THEO CHI NHÁNH =====
    case 'tonkho':
        $cn = $isAdmin ? (int)($_GET['cn'] ?? 0) : $myCn;
        $cns = $isAdmin ? ChiNhanh::tatCa() : [ChiNhanh::laySo($myCn)];
        if (!$cn && $cns) $cn = (int)$cns[0]['id'];
        aview('tonkho', [
            'cn' => $cn, 'cns' => $cns, 'ds' => $cn ? MonAn::tonTheoChiNhanh($cn) : [],
            'history' => $cn ? MonAn::lichSuTon($cn) : [],
        ]);
    case 'luutonkho':
        $cn = $isAdmin ? (int)post('cn') : $myCn;
        if (!ChiNhanh::laySo($cn)) { flash('danger', 'Chi nhánh không hợp lệ.'); redirect('index.php?action=tonkho'); }
        $ton = $_POST['ton'] ?? []; $pv = $_POST['phucvu'] ?? [];
        try {
            if (!is_array($ton) || !is_array($pv)) throw new RuntimeException('Dữ liệu tồn kho không hợp lệ.');
            $updates = [];
            foreach ($ton as $mid => $sl) {
                $foodId = filter_var($mid, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                $quantity = filter_var($sl, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 2147483647]]);
                if ($foodId === false || $quantity === false) throw new RuntimeException('Số lượng tồn phải là số nguyên không âm.');
                $updates[$foodId] = ['soluong' => $quantity, 'phucvu' => isset($pv[$mid])];
            }
            MonAn::capNhatTonNhieu($cn, $updates, (int)$user['id']);
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
            redirect('index.php?action=tonkho&cn=' . $cn);
        }
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
        if ($d['phuongthuc_tt'] === 'bank_transfer' && $d['trangthai_tt'] === 'pending'
            && !empty($d['thanhtoan_het_han']) && strtotime($d['thanhtoan_het_han']) <= time()) {
            DonHang::hetHanChuyenKhoan((int)$d['id']);
            $d = DonHang::laySo((int)$d['id']);
        }
        aview('donhang_ct', ['d' => $d, 'ct' => DonHang::chiTiet($d['id']), 'history' => DonHang::lichSu($d['id'])]);
    case 'capnhatdon':
        try { DonHang::capNhatTrangThai((int)post('id'), (int)post('trangthai'), $myCn, (int)$user['id']); flash('success', 'Đã cập nhật trạng thái đơn.'); }
        catch (RuntimeException $e) { flash('danger', $e->getMessage()); }
        redirect('index.php?action=chitietdon&id=' . (int)post('id'));
    case 'ghinhantienmat':
        try {
            DonHang::ghiNhanTienMat((int)post('id'), (int)$user['id'], $isAdmin ? null : $myCn);
            flash('success', 'Đã ghi nhận thu tiền mặt.');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('index.php?action=chitietdon&id=' . (int)post('id'));
    case 'ghinhanchuyenkhoan':
        try {
            DonHang::ghiNhanChuyenKhoan((int)post('id'), (int)$user['id'], $isAdmin ? null : $myCn);
            flash('success', 'Đã xác nhận nhận chuyển khoản và chuyển đơn sang chờ xử lý.');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('index.php?action=chitietdon&id=' . (int)post('id'));

    // ===== ĐẶT BÀN =====
    case 'datban':
        $cn = $isAdmin ? (int)($_GET['cn'] ?? 0) : $myCn;
        $tt = $_GET['tt'] ?? '';
        aview('datban', [
            'ds' => DatBan::danhSach($cn ?: null, $tt === '' ? null : (int)$tt, 200),
            'cn' => $cn, 'tt' => $tt, 'cns' => $isAdmin ? ChiNhanh::tatCa() : [],
        ]);
    case 'capnhatdatban':
        try {
            DatBan::capNhatTrangThai((int)post('id'), (int)post('trangthai'), $myCn, (int)$user['id']);
            flash('success', 'Đã cập nhật trạng thái yêu cầu đặt bàn.');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('index.php?action=datban');

    // ===== VOUCHER =====
    case 'voucher':
        only_admin();
        aview('voucher', [
            'ds' => Voucher::tatCa(),
            'edit' => !empty($_GET['edit']) ? Voucher::laySo((int)$_GET['edit']) : null,
            'cns' => ChiNhanh::tatCa(),
        ]);
    case 'luuvoucher':
        only_admin();
        try {
            Voucher::luu($_POST);
            flash('success', 'Đã lưu voucher.');
        } catch (RuntimeException $e) {
            flash('danger', $e->getMessage());
        }
        redirect('index.php?action=voucher' . ((int)post('id') ? '&edit=' . (int)post('id') : ''));
    case 'doitrangthaivoucher':
        only_admin();
        if (Voucher::doiTrangThai((int)post('id'))) flash('success', 'Đã đổi trạng thái voucher.');
        else flash('danger', 'Không tìm thấy voucher.');
        redirect('index.php?action=voucher');

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
        $requestedDays = (int)($_GET['days'] ?? 30);
        $days = in_array($requestedDays, [7, 30, 90], true) ? $requestedDays : 30;
        aview('baocao', [
            'days' => $days,
            'cnRev' => $isAdmin ? ThongKe::doanhThuTheoChiNhanh($days) : [],
            'ngay' => ThongKe::doanhThuTheoNgay($myCn, $days),
            'top' => ThongKe::topMon($myCn, $days, 10),
        ]);
}
