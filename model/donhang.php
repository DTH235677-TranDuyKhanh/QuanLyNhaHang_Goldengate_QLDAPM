<?php
class DonHang {
    // Tạo đơn trong 1 giao dịch: khóa dòng tồn kho -> kiểm tra -> ghi đơn -> trừ kho
    public static function tao($uid, $cn, $hinhthuc, $hoten, $sdt, $diachi, $ghichu, array $items) {
        if (!$items) throw new RuntimeException('Giỏ hàng trống.');
        ksort($items);   // khóa theo thứ tự cố định để tránh deadlock
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $lines = []; $tam = 0;
            foreach ($items as $id => $sl) {
                $sl = (int)$sl;
                $r = DB::one("SELECT m.tenmon, m.giaban, m.trangthai, mc.soluongton, mc.conphucvu
                              FROM monan_chinhanh mc JOIN monan m ON m.id = mc.monan_id
                              WHERE mc.chinhanh_id = ? AND mc.monan_id = ? FOR UPDATE", [(int)$cn, (int)$id]);
                if (!$r || !$r['trangthai'] || !$r['conphucvu']) {
                    throw new RuntimeException('Món "' . ($r['tenmon'] ?? ('#' . $id)) . '" hiện không phục vụ tại chi nhánh này.');
                }
                if ((int)$r['soluongton'] < $sl) {
                    throw new RuntimeException('Món "' . $r['tenmon'] . '" chỉ còn ' . (int)$r['soluongton'] . ' phần.');
                }
                $tt = $sl * (float)$r['giaban'];
                $tam += $tt;
                $lines[] = [(int)$id, $r['tenmon'], $r['giaban'], $sl, $tt];
            }
            $phi = GioHang::phiGiao($hinhthuc, $tam);
            $did = DB::insert("INSERT INTO donhang (nguoidung_id, chinhanh_id, hinhthuc, hoten_nhan, sodt_nhan, diachi, phigiao, tongtien, ghichu)
                               VALUES (?,?,?,?,?,?,?,?,?)",
                [(int)$uid, (int)$cn, (int)$hinhthuc, $hoten, $sdt, $diachi ?: null, $phi, $tam + $phi, $ghichu ?: null]);
            foreach ($lines as [$mid, $ten, $gia, $sl, $tt]) {
                DB::exec("INSERT INTO donhangct (donhang_id, monan_id, tenmon, dongia, soluong, thanhtien) VALUES (?,?,?,?,?,?)",
                    [$did, $mid, $ten, $gia, $sl, $tt]);
                DB::exec("UPDATE monan_chinhanh SET soluongton = soluongton - ? WHERE chinhanh_id = ? AND monan_id = ?", [$sl, (int)$cn, $mid]);
                DB::exec("UPDATE monan SET luotgoi = luotgoi + ? WHERE id = ?", [$sl, $mid]);
            }
            $pdo->commit();
            return $did;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    // Đổi trạng thái: chỉ đi tiến hoặc hủy; hủy sẽ hoàn lại tồn kho. $gioiHanCn: quản lý chỉ thao tác đơn chi nhánh mình
    public static function capNhatTrangThai($id, $moi, $gioiHanCn = null) {
        $moi = (int)$moi;
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $d = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$d) throw new RuntimeException('Không tìm thấy đơn hàng.');
            if ($gioiHanCn !== null && (int)$d['chinhanh_id'] !== (int)$gioiHanCn) throw new RuntimeException('Bạn không có quyền với đơn này.');
            $cu = (int)$d['trangthai'];
            if (in_array($cu, [3, 4], true)) throw new RuntimeException('Đơn đã kết thúc, không thể thay đổi.');
            if ($moi < 0 || $moi > 4) throw new RuntimeException('Trạng thái không hợp lệ.');
            if ($moi !== 4 && $moi <= $cu) throw new RuntimeException('Không thể lùi trạng thái đơn.');
            if ($moi === 4) {
                foreach (DB::all("SELECT monan_id, soluong FROM donhangct WHERE donhang_id = ?", [(int)$id]) as $ct) {
                    DB::exec("UPDATE monan_chinhanh SET soluongton = soluongton + ? WHERE chinhanh_id = ? AND monan_id = ?",
                        [$ct['soluong'], $d['chinhanh_id'], $ct['monan_id']]);
                    DB::exec("UPDATE monan SET luotgoi = GREATEST(luotgoi - ?, 0) WHERE id = ?", [$ct['soluong'], $ct['monan_id']]);
                }
            }
            DB::exec("UPDATE donhang SET trangthai = ? WHERE id = ?", [$moi, (int)$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    // Khách tự hủy: chỉ khi đơn còn "Chờ xác nhận" và là đơn của mình
    public static function khachHuy($id, $uid) {
        $d = self::laySo($id);
        if (!$d || (int)$d['nguoidung_id'] !== (int)$uid) throw new RuntimeException('Không tìm thấy đơn hàng.');
        if ((int)$d['trangthai'] !== 0) throw new RuntimeException('Đơn đã được xác nhận, vui lòng liên hệ nhà hàng để hủy.');
        self::capNhatTrangThai($id, 4);
    }

    public static function laySo($id) {
        return DB::one("SELECT d.*, c.tenchinhanh FROM donhang d JOIN chinhanh c ON c.id = d.chinhanh_id WHERE d.id = ?", [(int)$id]);
    }
    public static function chiTiet($id) { return DB::all("SELECT * FROM donhangct WHERE donhang_id = ? ORDER BY id", [(int)$id]); }
    public static function cuaNguoiDung($uid) {
        return DB::all("SELECT d.*, c.tenchinhanh FROM donhang d JOIN chinhanh c ON c.id = d.chinhanh_id
                        WHERE d.nguoidung_id = ? ORDER BY d.id DESC", [(int)$uid]);
    }
    public static function traCuu($id, $sdt) {
        return DB::one("SELECT d.*, c.tenchinhanh FROM donhang d JOIN chinhanh c ON c.id = d.chinhanh_id
                        WHERE d.id = ? AND d.sodt_nhan = ?", [(int)$id, $sdt]);
    }
    // Danh sách cho quản trị (lọc theo chi nhánh/trạng thái)
    public static function danhSach($cn = null, $tt = null, $limit = 100) {
        $sql = "SELECT d.*, c.tenchinhanh FROM donhang d JOIN chinhanh c ON c.id = d.chinhanh_id WHERE 1=1";
        $p = [];
        if ($cn) { $sql .= " AND d.chinhanh_id = ?"; $p[] = (int)$cn; }
        if ($tt !== null && $tt !== '') { $sql .= " AND d.trangthai = ?"; $p[] = (int)$tt; }
        $sql .= " ORDER BY d.id DESC LIMIT " . max(1, (int)$limit);
        return DB::all($sql, $p);
    }
}
