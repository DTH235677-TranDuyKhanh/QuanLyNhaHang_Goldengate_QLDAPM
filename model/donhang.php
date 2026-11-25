<?php
class DonHang {
    // Lock inventory and voucher, then create the order and update both atomically.
    public static function tao($uid, $cn, $hinhthuc, $hoten, $sdt, $diachi, $ghichu, array $items, $voucherCode = '', $paymentMethod = 'cod') {
        if (!$items) throw new RuntimeException('Giỏ hàng trống.');
        if (!in_array($paymentMethod, ['cod', 'bank_transfer'], true)) throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
        if ($paymentMethod === 'bank_transfer') {
            $paymentColumn = DB::one("SHOW COLUMNS FROM donhang LIKE 'phuongthuc_tt'");
            if (!$paymentColumn || strpos((string)$paymentColumn['Type'], "'bank_transfer'") === false) {
                throw new RuntimeException('Cơ sở dữ liệu chưa hỗ trợ chuyển khoản. Hãy cập nhật cột phuongthuc_tt để bao gồm bank_transfer.');
            }
        }
        ksort($items);   // khóa theo thứ tự cố định để tránh deadlock
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $lines = []; $tam = 0;
            foreach ($items as $id => $sl) {
                $sl = (int)$sl;
                if ($sl < 1 || $sl > 20) throw new RuntimeException('Số lượng mỗi món phải từ 1 đến 20.');
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
            $voucherResult = Voucher::tinhGiam($voucherCode, $cn, $tam, true);
            $voucher = $voucherResult['voucher'];
            $discount = $voucherResult['giamgia'];
            $total = $tam + $phi - $discount;
            if ($paymentMethod === 'bank_transfer' && $total < 1) {
                throw new RuntimeException('Tổng tiền phải lớn hơn 0 để thanh toán chuyển khoản.');
            }
            $did = DB::insert("INSERT INTO donhang
                (nguoidung_id, chinhanh_id, hinhthuc, hoten_nhan, sodt_nhan, diachi, phigiao, voucher_id, giamgia, tongtien, ghichu, phuongthuc_tt)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)",
                [(int)$uid, (int)$cn, (int)$hinhthuc, $hoten, $sdt, $diachi ?: null, $phi,
                 $voucher ? (int)$voucher['id'] : null, $discount, $total, $ghichu ?: null, $paymentMethod]);
            DB::exec("INSERT INTO donhang_lichsu (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                      VALUES (?,NULL,0,?,'Khách tạo đơn')", [$did, (int)$uid]);
            if ($voucher && Voucher::danhDauDaDung($voucher['id']) !== 1) {
                throw new RuntimeException('Không thể áp dụng voucher do đã hết lượt sử dụng.');
            }
            foreach ($lines as [$mid, $ten, $gia, $sl, $tt]) {
                DB::exec("INSERT INTO donhangct (donhang_id, monan_id, tenmon, dongia, soluong, thanhtien) VALUES (?,?,?,?,?,?)",
                    [$did, $mid, $ten, $gia, $sl, $tt]);
                DB::exec("UPDATE monan_chinhanh SET soluongton = soluongton - ? WHERE chinhanh_id = ? AND monan_id = ?", [$sl, (int)$cn, $mid]);
                DB::exec("INSERT INTO tonkho_lichsu
                    (chinhanh_id, monan_id, bien_dong, loai, tham_chieu, ghi_chu, nguoidung_id)
                    VALUES (?,?,?,'ban_hang',?,'Xuất kho khi tạo đơn',?)",
                    [(int)$cn, $mid, -$sl, 'DH' . $did, (int)$uid]);
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
    public static function capNhatTrangThai($id, $moi, $gioiHanCn = null, $actorId = null, $expectedCurrent = null) {
        if (filter_var($moi, FILTER_VALIDATE_INT) === false) {
            throw new RuntimeException('Trạng thái đơn hàng không hợp lệ.');
        }
        $moi = (int)$moi;
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $d = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$d) throw new RuntimeException('Không tìm thấy đơn hàng.');
            if ($gioiHanCn !== null && (int)$d['chinhanh_id'] !== (int)$gioiHanCn) throw new RuntimeException('Bạn không có quyền với đơn này.');
            $cu = (int)$d['trangthai'];
            if (in_array($cu, [3, 4], true)) throw new RuntimeException('Đơn đã kết thúc, không thể thay đổi.');
            if (in_array($d['phuongthuc_tt'], ['bank_transfer', 'vnpay'], true)
                && $d['trangthai_tt'] !== 'paid' && $moi !== 4) {
                throw new RuntimeException('Chỉ xử lý đơn chuyển khoản sau khi nhân viên xác nhận đã nhận tiền.');
            }
            if ($moi === 4 && $d['trangthai_tt'] === 'paid') {
                throw new RuntimeException('Đơn đã thanh toán. Hãy xử lý hoàn tiền với khách trước khi hủy đơn.');
            }
            if ($moi === 3 && $d['trangthai_tt'] !== 'paid') {
                throw new RuntimeException('Chỉ hoàn thành đơn sau khi đã ghi nhận thanh toán.');
            }
            if ($moi < 0 || $moi > 4) throw new RuntimeException('Trạng thái không hợp lệ.');
            if ($moi !== 4 && $moi !== $cu + 1) throw new RuntimeException('Đơn phải được chuyển trạng thái theo đúng từng bước.');
            if ($expectedCurrent !== null && $cu !== (int)$expectedCurrent) {
                throw new RuntimeException('Trạng thái đơn đã thay đổi. Vui lòng tải lại trang.');
            }
            if ($moi === 4) {
                self::hoanKhoTrongGiaoDich($d, $actorId, 'Hoàn kho do hủy đơn');
            }
            if ($moi === 4 && $d['phuongthuc_tt'] === 'bank_transfer' && $d['trangthai_tt'] === 'pending') {
                DB::exec("UPDATE donhang SET trangthai = ?, trangthai_tt = 'expired' WHERE id = ?", [$moi, (int)$id]);
            } else {
                DB::exec("UPDATE donhang SET trangthai = ? WHERE id = ?", [$moi, (int)$id]);
            }
            DB::exec("INSERT INTO donhang_lichsu
                (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                VALUES (?,?,?,?,?)",
                [(int)$id, $cu, $moi, $actorId ? (int)$actorId : null,
                 $moi === 4 ? 'Đơn hàng bị hủy' : 'Cập nhật trạng thái đơn']);
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
        self::capNhatTrangThai($id, 4, null, $uid, 0);
    }

    public static function laySo($id) {
        return DB::one("SELECT d.*, c.tenchinhanh, v.ma AS voucher_ma
                        FROM donhang d
                        JOIN chinhanh c ON c.id = d.chinhanh_id
                        LEFT JOIN voucher v ON v.id = d.voucher_id
                        WHERE d.id = ?", [(int)$id]);
    }
    public static function chiTiet($id) { return DB::all("SELECT * FROM donhangct WHERE donhang_id = ? ORDER BY id", [(int)$id]); }
    public static function lichSu($id) {
        return DB::all("SELECT l.*, u.hoten AS nguoi_thay_doi
                        FROM donhang_lichsu l
                        LEFT JOIN nguoidung u ON u.id = l.nguoidung_id
                        WHERE l.donhang_id = ? ORDER BY l.id", [(int)$id]);
    }

    public static function batDauChuyenKhoan($id, $userId) {
        $bankConfig = cfg('bank_transfer') ?: [];
        $minutes = max(1, (int)($bankConfig['expire_minutes'] ?? 15));
        if (empty($bankConfig['bank_code']) || empty($bankConfig['account_number']) || empty($bankConfig['account_name'])) {
            throw new RuntimeException('Chưa cấu hình tài khoản nhận chuyển khoản.');
        }

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $order = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$order || (int)$order['nguoidung_id'] !== (int)$userId) {
                throw new RuntimeException('Không tìm thấy đơn hàng để thanh toán.');
            }
            if ((int)$order['trangthai'] !== 0 || $order['trangthai_tt'] !== 'unpaid'
                || $order['phuongthuc_tt'] !== 'bank_transfer') {
                throw new RuntimeException('Đơn hàng không còn ở trạng thái có thể thanh toán.');
            }

            $expiresAt = date('Y-m-d H:i:s', time() + $minutes * 60);
            DB::exec("UPDATE donhang SET trangthai_tt = 'pending', thanhtoan_het_han = ? WHERE id = ?",
                [$expiresAt, (int)$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function ghiNhanChuyenKhoan($id, $actorId, $branchLimit = null) {
        $check = self::laySo((int)$id);
        if (!$check) throw new RuntimeException('Không tìm thấy đơn hàng.');
        if ($branchLimit !== null && (int)$check['chinhanh_id'] !== (int)$branchLimit) {
            throw new RuntimeException('Bạn không có quyền cập nhật đơn của chi nhánh khác.');
        }
        if ($check['phuongthuc_tt'] !== 'bank_transfer') throw new RuntimeException('Đơn này không thanh toán chuyển khoản.');
        self::hetHanChuyenKhoan((int)$id);
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $order = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$order) throw new RuntimeException('Không tìm thấy đơn hàng.');
            if ($branchLimit !== null && (int)$order['chinhanh_id'] !== (int)$branchLimit) {
                throw new RuntimeException('Bạn không có quyền cập nhật đơn của chi nhánh khác.');
            }
            if ($order['phuongthuc_tt'] !== 'bank_transfer') throw new RuntimeException('Đơn này không thanh toán chuyển khoản.');
            if ((int)$order['trangthai'] !== 0 || $order['trangthai_tt'] !== 'pending') {
                throw new RuntimeException('Đơn không còn chờ xác nhận chuyển khoản.');
            }
            if (!empty($order['thanhtoan_het_han']) && strtotime($order['thanhtoan_het_han']) <= time()) {
                throw new RuntimeException('Đơn đã hết hạn thanh toán.');
            }

            DB::exec("UPDATE donhang SET trangthai = 1, trangthai_tt = 'paid', thanhtoan_luc = NOW() WHERE id = ?",
                [(int)$id]);
            DB::exec("INSERT INTO donhang_lichsu
                (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                VALUES (?,0,1,?,'Nhân viên xác nhận đã nhận chuyển khoản')",
                [(int)$id, (int)$actorId]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function hetHanChuyenKhoan($id = null) {
        if ($id !== null) {
            $ids = DB::all("SELECT id FROM donhang WHERE id = ? AND phuongthuc_tt = 'bank_transfer'
                            AND trangthai_tt = 'pending' AND thanhtoan_het_han <= NOW()", [(int)$id]);
        } else {
            $ids = DB::all("SELECT id FROM donhang WHERE phuongthuc_tt = 'bank_transfer'
                            AND trangthai_tt = 'pending' AND thanhtoan_het_han <= NOW()
                            ORDER BY id LIMIT 500");
        }
        $expired = 0;
        foreach ($ids as $row) {
            $pdo = DB::pdo();
            $pdo->beginTransaction();
            try {
                $order = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$row['id']]);
                if (!$order || $order['phuongthuc_tt'] !== 'bank_transfer'
                    || $order['trangthai_tt'] !== 'pending'
                    || empty($order['thanhtoan_het_han']) || strtotime($order['thanhtoan_het_han']) > time()) {
                    $pdo->rollBack();
                    continue;
                }

                if ((int)$order['trangthai'] === 0) {
                    self::hoanKhoTrongGiaoDich($order, null, 'Hoàn kho do hết hạn chuyển khoản');
                    DB::exec("UPDATE donhang SET trangthai = 4, trangthai_tt = 'expired' WHERE id = ?", [(int)$order['id']]);
                    DB::exec("INSERT INTO donhang_lichsu
                        (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                        VALUES (?,0,4,NULL,'Đơn hủy do hết thời gian chuyển khoản')", [(int)$order['id']]);
                } else {
                    DB::exec("UPDATE donhang SET trangthai_tt = 'expired' WHERE id = ?", [(int)$order['id']]);
                }
                $pdo->commit();
                $expired++;
            } catch (Throwable $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                throw $e;
            }
        }
        return $expired;
    }

    public static function ghiNhanTienMat($id, $actorId, $branchLimit = null) {
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $order = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$order) throw new RuntimeException('Không tìm thấy đơn hàng.');
            if ($branchLimit !== null && (int)$order['chinhanh_id'] !== (int)$branchLimit) {
                throw new RuntimeException('Bạn không có quyền cập nhật đơn của chi nhánh khác.');
            }
            if ($order['phuongthuc_tt'] !== 'cod') throw new RuntimeException('Chỉ ghi nhận thu tiền mặt cho đơn thanh toán tại nhà hàng.');
            if ((int)$order['trangthai'] === 4) throw new RuntimeException('Không thể thu tiền cho đơn đã hủy.');
            if ($order['trangthai_tt'] === 'paid') throw new RuntimeException('Đơn hàng đã được ghi nhận thanh toán.');
            DB::exec("UPDATE donhang SET trangthai_tt = 'paid', thanhtoan_luc = NOW() WHERE id = ?", [(int)$id]);
            DB::exec("INSERT INTO donhang_lichsu
                (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                VALUES (?,?,?,?,?)",
                [(int)$id, (int)$order['trangthai'], (int)$order['trangthai'], (int)$actorId, 'Đã thu tiền mặt']);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    private static function hoanKhoTrongGiaoDich(array $order, $actorId, $note) {
        foreach (DB::all("SELECT monan_id, soluong FROM donhangct WHERE donhang_id = ?", [(int)$order['id']]) as $item) {
            DB::exec("UPDATE monan_chinhanh SET soluongton = soluongton + ?
                      WHERE chinhanh_id = ? AND monan_id = ?",
                [(int)$item['soluong'], (int)$order['chinhanh_id'], (int)$item['monan_id']]);
            DB::exec("INSERT INTO tonkho_lichsu
                (chinhanh_id, monan_id, bien_dong, loai, tham_chieu, ghi_chu, nguoidung_id)
                VALUES (?,?,?,'hoan_hang',?,?,?)",
                [(int)$order['chinhanh_id'], (int)$item['monan_id'], (int)$item['soluong'],
                 'DH' . (int)$order['id'], $note, $actorId ? (int)$actorId : null]);
            DB::exec("UPDATE monan SET luotgoi = GREATEST(luotgoi - ?, 0) WHERE id = ?",
                [(int)$item['soluong'], (int)$item['monan_id']]);
        }
        if (!empty($order['voucher_id'])) Voucher::hoanLuotSuDung($order['voucher_id']);
    }
    public static function cuaNguoiDung($uid) {
        return DB::all("SELECT d.*, c.tenchinhanh, v.ma AS voucher_ma
                        FROM donhang d JOIN chinhanh c ON c.id = d.chinhanh_id
                        LEFT JOIN voucher v ON v.id = d.voucher_id
                        WHERE d.nguoidung_id = ? ORDER BY d.id DESC", [(int)$uid]);
    }
    public static function traCuu($id, $sdt) {
        return DB::one("SELECT d.*, c.tenchinhanh, v.ma AS voucher_ma
                        FROM donhang d JOIN chinhanh c ON c.id = d.chinhanh_id
                        LEFT JOIN voucher v ON v.id = d.voucher_id
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
