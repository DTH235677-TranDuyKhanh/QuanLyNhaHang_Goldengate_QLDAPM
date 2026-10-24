<?php
class NguoiDung {
    public static function timTheoSdt($sdt) { return DB::one("SELECT * FROM nguoidung WHERE sodienthoai = ?", [$sdt]); }
    public static function timTheoEmail($e) { return DB::one("SELECT * FROM nguoidung WHERE email = ?", [$e]); }
    public static function laySo($id) { return DB::one("SELECT * FROM nguoidung WHERE id = ?", [(int)$id]); }

    // Đăng nhập bằng email hoặc số điện thoại
    public static function dangNhap($dinhdanh, $matkhau) {
        $u = DB::one("SELECT * FROM nguoidung WHERE (email = ? OR sodienthoai = ?) AND dadangky = 1", [$dinhdanh, $dinhdanh]);
        if (!$u || !password_verify($matkhau, $u['matkhau'])) return null;
        if ((int)$u['trangthai'] !== 1) return false;   // tài khoản bị khóa
        return $u;
    }

    // Đăng ký. Nếu SĐT từng đặt món như khách vãng lai thì "nhận lại" lịch sử đó.
    public static function dangKy($hoten, $sdt, $email, $matkhau) {
        $hash = password_hash($matkhau, PASSWORD_DEFAULT);
        $email = $email !== '' ? $email : null;
        $cu = self::timTheoSdt($sdt);
        if ($email !== null) {
            $e = self::timTheoEmail($email);
            if ($e && (!$cu || (int)$e['id'] !== (int)$cu['id'])) throw new RuntimeException('Email đã được sử dụng.');
        }
        if ($cu) {
            if ((int)$cu['dadangky'] === 1) throw new RuntimeException('Số điện thoại đã có tài khoản.');
            DB::exec("UPDATE nguoidung SET hoten=?, email=?, matkhau=?, dadangky=1 WHERE id=?", [$hoten, $email, $hash, $cu['id']]);
            return (int)$cu['id'];
        }
        return DB::insert("INSERT INTO nguoidung (email, sodienthoai, matkhau, hoten, loai, dadangky) VALUES (?,?,?,?,3,1)",
            [$email, $sdt, $hash, $hoten]);
    }

    // Khách vãng lai: tạo hồ sơ không thể đăng nhập (mật khẩu ngẫu nhiên)
    public static function taoKhachVangLai($hoten, $sdt) {
        $hash = password_hash(bin2hex(random_bytes(16)), PASSWORD_DEFAULT);
        return DB::insert("INSERT INTO nguoidung (email, sodienthoai, matkhau, hoten, loai, dadangky) VALUES (NULL,?,?,?,3,0)",
            [$sdt, $hash, $hoten]);
    }

    public static function diaChiMacDinh($uid) {
        return DB::val("SELECT diachi FROM diachi WHERE nguoidung_id = ? ORDER BY macdinh DESC, id DESC LIMIT 1", [(int)$uid]) ?: '';
    }
    public static function luuDiaChi($uid, $diachi) {
        if (DB::val("SELECT COUNT(*) FROM diachi WHERE nguoidung_id = ? AND diachi = ?", [(int)$uid, $diachi])) return;
        DB::exec("UPDATE diachi SET macdinh = 0 WHERE nguoidung_id = ?", [(int)$uid]);
        DB::insert("INSERT INTO diachi (nguoidung_id, diachi, macdinh) VALUES (?,?,1)", [(int)$uid, $diachi]);
    }
    public static function doiMatKhau($uid, $cu, $moi) {
        $u = self::laySo($uid);
        if (!$u || !password_verify($cu, $u['matkhau'])) return false;
        DB::exec("UPDATE nguoidung SET matkhau = ? WHERE id = ?", [password_hash($moi, PASSWORD_DEFAULT), (int)$uid]);
        return true;
    }

    // ---------- Quản trị ----------
    public static function danhSach() {
        return DB::all("SELECT u.*, c.tenchinhanh FROM nguoidung u LEFT JOIN chinhanh c ON c.id = u.chinhanh_id
                        WHERE u.dadangky = 1 ORDER BY u.loai, u.id");
    }
    public static function taoNhanSu($hoten, $sdt, $email, $matkhau, $loai, $chinhanh) {
        if (self::timTheoSdt($sdt)) throw new RuntimeException('Số điện thoại đã tồn tại.');
        if ($email !== '' && self::timTheoEmail($email)) throw new RuntimeException('Email đã tồn tại.');
        return DB::insert("INSERT INTO nguoidung (email, sodienthoai, matkhau, hoten, loai, chinhanh_id, dadangky) VALUES (?,?,?,?,?,?,1)",
            [$email !== '' ? $email : null, $sdt, password_hash($matkhau, PASSWORD_DEFAULT), $hoten, (int)$loai, $chinhanh ?: null]);
    }
    public static function doiTrangThai($id) { return DB::exec("UPDATE nguoidung SET trangthai = 1 - trangthai WHERE id = ?", [(int)$id]); }
}
