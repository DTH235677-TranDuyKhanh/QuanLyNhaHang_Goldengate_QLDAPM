<?php
class DonHang {
    // Lock inventory and voucher, then create the order and update both atomically.
    public static function tao($uid, $cn, $hinhthuc, $hoten, $sdt, $diachi, $ghichu, array $items, $voucherCode = '', $paymentMethod = 'cod') {
        if (!$items) throw new RuntimeException('Giỏ hàng trống.');
        if (!in_array($paymentMethod, ['cod', 'vnpay'], true)) throw new RuntimeException('Phương thức thanh toán không hợp lệ.');
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
            if ($paymentMethod === 'vnpay' && $total < 1) {
                throw new RuntimeException('Tổng tiền phải lớn hơn 0 để thanh toán qua VNPay.');
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
            if ($d['phuongthuc_tt'] === 'vnpay' && $d['trangthai_tt'] !== 'paid' && $moi !== 4) {
                throw new RuntimeException('Chỉ có thể xác nhận đơn VNPay sau khi giao dịch được máy chủ đối soát thành công.');
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
            DB::exec("UPDATE donhang SET trangthai = ? WHERE id = ?", [$moi, (int)$id]);
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

    public static function batDauThanhToanVnPay($id, $userId, $reference, $expiresAt) {
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $order = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$order || (int)$order['nguoidung_id'] !== (int)$userId) {
                throw new RuntimeException('Không tìm thấy đơn hàng để thanh toán.');
            }
            if ((int)$order['trangthai'] !== 0 || $order['trangthai_tt'] !== 'unpaid' || $order['phuongthuc_tt'] !== 'vnpay') {
                throw new RuntimeException('Đơn hàng không còn ở trạng thái có thể thanh toán.');
            }
            DB::exec("UPDATE donhang SET phuongthuc_tt = 'vnpay', trangthai_tt = 'pending',
                      vnpay_txn_ref = ?, thanhtoan_het_han = ? WHERE id = ?",
                [(string)$reference, $expiresAt, (int)$id]);
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
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

    public static function xuLyThongBaoVnPay(array $params) {
        $reference = trim((string)($params['vnp_TxnRef'] ?? ''));
        if ($reference === '' || !isset($params['vnp_Amount'], $params['vnp_ResponseCode'], $params['vnp_TransactionNo'])) return '01';

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $order = DB::one("SELECT * FROM donhang WHERE vnpay_txn_ref = ? FOR UPDATE", [$reference]);
            if (!$order) { $pdo->rollBack(); return '01'; }
            if ((string)$params['vnp_Amount'] !== (string)((int)$order['tongtien'] * 100)) {
                $pdo->rollBack();
                return '04';
            }
            $transactionNo = trim((string)$params['vnp_TransactionNo']);
            $success = (string)$params['vnp_ResponseCode'] === '00'
                && (string)($params['vnp_TransactionStatus'] ?? '') === '00';
            if ($success && $transactionNo === '') { $pdo->rollBack(); return '04'; }
            if ($order['trangthai_tt'] === 'paid' || $order['trangthai_tt'] === 'manual_review') {
                $pdo->rollBack();
                return hash_equals((string)$order['vnpay_transaction_no'], $transactionNo) ? '00' : '02';
            }
            if ($order['trangthai_tt'] !== 'pending') {
                if ($success && (int)$order['trangthai'] === 4) {
                    DB::exec("UPDATE donhang SET trangthai_tt = 'manual_review',
                              vnpay_transaction_no = ?, vnpay_response_code = ?, thanhtoan_luc = NOW()
                              WHERE id = ?",
                        [$transactionNo, (string)$params['vnp_ResponseCode'], (int)$order['id']]);
                    DB::exec("INSERT INTO donhang_lichsu
                        (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                        VALUES (?,4,4,NULL,'VNPay báo tiền đã thu cho đơn đã hủy/hết hạn; cần đối soát')",
                        [(int)$order['id']]);
                    $pdo->commit();
                    return '00';
                }
                $pdo->rollBack();
                return in_array($order['trangthai_tt'], ['failed', 'expired'], true) ? '00' : '02';
            }

            $late = !empty($order['thanhtoan_het_han'])
                && strtotime($order['thanhtoan_het_han']) < time();
            if ($success && (int)$order['trangthai'] === 0 && !$late) {
                DB::exec("UPDATE donhang SET trangthai = 1, trangthai_tt = 'paid',
                          vnpay_transaction_no = ?, vnpay_response_code = ?, thanhtoan_luc = NOW()
                          WHERE id = ?",
                    [$transactionNo, (string)$params['vnp_ResponseCode'], (int)$order['id']]);
                DB::exec("INSERT INTO donhang_lichsu
                    (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                    VALUES (?,0,1,NULL,'VNPay xác nhận thanh toán thành công')", [(int)$order['id']]);
            } elseif ($success) {
                if ((int)$order['trangthai'] === 0) {
                    self::hoanKhoTrongGiaoDich($order, null, 'Hoàn kho do thanh toán VNPay đến sau thời hạn');
                    DB::exec("UPDATE donhang SET trangthai = 4 WHERE id = ?", [(int)$order['id']]);
                    DB::exec("INSERT INTO donhang_lichsu
                        (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                        VALUES (?,0,4,NULL,'Đơn hủy do VNPay báo thanh toán sau thời hạn')", [(int)$order['id']]);
                }
                DB::exec("UPDATE donhang SET trangthai_tt = 'manual_review',
                          vnpay_transaction_no = ?, vnpay_response_code = ?, thanhtoan_luc = NOW()
                          WHERE id = ?",
                    [$transactionNo, (string)$params['vnp_ResponseCode'], (int)$order['id']]);
                DB::exec("INSERT INTO donhang_lichsu
                    (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                    VALUES (?,?,?,?,?)",
                    [(int)$order['id'], (int)$order['trangthai'], (int)$order['trangthai'], null,
                     'VNPay báo đã thu tiền nhưng đơn đã quá hạn hoặc không còn chờ xử lý; cần đối soát']);
            } else {
                if ((int)$order['trangthai'] === 0) {
                    self::hoanKhoTrongGiaoDich($order, null, 'Hoàn kho do giao dịch VNPay thất bại');
                    DB::exec("UPDATE donhang SET trangthai = 4 WHERE id = ?", [(int)$order['id']]);
                    DB::exec("INSERT INTO donhang_lichsu
                        (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                        VALUES (?,0,4,NULL,'Đơn hủy do VNPay báo thanh toán thất bại')", [(int)$order['id']]);
                    $paymentStatus = 'failed';
                } elseif ((int)$order['trangthai'] === 4) {
                    $paymentStatus = 'failed';
                } else {
                    $paymentStatus = 'manual_review';
                }
                DB::exec("UPDATE donhang SET trangthai_tt = ?, vnpay_transaction_no = ?,
                          vnpay_response_code = ? WHERE id = ?",
                    [$paymentStatus, $transactionNo, (string)$params['vnp_ResponseCode'], (int)$order['id']]);
            }
            $pdo->commit();
            return '00';
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function hetHanThanhToanVnPay() {
        $ids = DB::all("SELECT id FROM donhang WHERE phuongthuc_tt = 'vnpay'
                        AND trangthai_tt = 'pending' AND thanhtoan_het_han <= NOW()
                        ORDER BY id LIMIT 500");
        $expired = 0;
        foreach ($ids as $row) {
            $pdo = DB::pdo();
            $pdo->beginTransaction();
            try {
                $order = DB::one("SELECT * FROM donhang WHERE id = ? FOR UPDATE", [(int)$row['id']]);
                if (!$order || $order['trangthai_tt'] !== 'pending'
                    || empty($order['thanhtoan_het_han']) || strtotime($order['thanhtoan_het_han']) > time()) {
                    $pdo->rollBack();
                    continue;
                }

                if ((int)$order['trangthai'] === 0) {
                    self::hoanKhoTrongGiaoDich($order, null, 'Hoàn kho do hết hạn thanh toán VNPay');
                    DB::exec("UPDATE donhang SET trangthai = 4, trangthai_tt = 'expired' WHERE id = ?", [(int)$order['id']]);
                    DB::exec("INSERT INTO donhang_lichsu
                        (donhang_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                        VALUES (?,0,4,NULL,'Đơn hủy do hết thời gian thanh toán VNPay')", [(int)$order['id']]);
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

    public static function theoMaGiaoDichVnPay($reference) {
        return DB::one("SELECT d.*, c.tenchinhanh, v.ma AS voucher_ma
                        FROM donhang d
                        JOIN chinhanh c ON c.id = d.chinhanh_id
                        LEFT JOIN voucher v ON v.id = d.voucher_id
                        WHERE d.vnpay_txn_ref = ?", [(string)$reference]);
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
