<?php
class DatBan {
    const CHO_XAC_NHAN = 0;
    const DA_XAC_NHAN = 1;
    const DA_DEN = 2;
    const DA_HUY = 3;
    const KHONG_DEN = 4;
    const TU_CHOI = 5;

    public static function tao(array $data, $userId = null) {
        $branchId = (int)($data['chinhanh_id'] ?? 0);
        $branch = ChiNhanh::laySo($branchId);
        if (!$branch || !(int)$branch['trangthai']) throw new RuntimeException('Chi nhánh không hoạt động hoặc không tồn tại.');

        $name = trim((string)($data['hoten'] ?? ''));
        $phone = trim((string)($data['sdt'] ?? ''));
        $guests = filter_var($data['so_khach'] ?? null, FILTER_VALIDATE_INT);
        $notes = trim((string)($data['ghichu'] ?? ''));
        $time = self::parseDate((string)($data['thoi_gian'] ?? ''));

        $nameLength = preg_match_all('/./us', $name, $matches);
        $noteLength = preg_match_all('/./us', $notes, $matches);
        if ($name === '' || $nameLength === false || $nameLength > 255) throw new RuntimeException('Vui lòng nhập họ tên hợp lệ.');
        if (!preg_match('/^0\d{9}$/', $phone)) throw new RuntimeException('Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.');
        if ($guests === false || $guests < 1 || $guests > 30) throw new RuntimeException('Số khách phải là số nguyên từ 1 đến 30.');
        if ($guests > (int)$branch['suc_chua']) throw new RuntimeException('Số khách vượt sức chứa đặt bàn tối đa của chi nhánh.');
        if (!$time || $time <= new DateTimeImmutable('now')) throw new RuntimeException('Vui lòng chọn thời gian đặt bàn trong tương lai.');
        if ($noteLength === false || $noteLength > 500) throw new RuntimeException('Ghi chú không được vượt quá 500 ký tự.');

        $open = substr((string)$branch['giomocua'], 0, 5);
        $close = substr((string)$branch['giodongcua'], 0, 5);
        $at = $time->format('H:i');
        $withinHours = $close >= $open
            ? ($at >= $open && $at < $close)
            : ($at >= $open || $at < $close);
        if (!$withinHours) {
            throw new RuntimeException('Thời gian đặt bàn nằm ngoài giờ hoạt động của chi nhánh (' . $open . '–' . $close . ').');
        }

        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $bookingId = DB::insert("INSERT INTO datban
                (nguoidung_id, chinhanh_id, hoten, sodienthoai, thoi_gian, so_khach, ghichu, trangthai)
                VALUES (?,?,?,?,?,?,?,?)",
                [$userId ? (int)$userId : null, $branchId, $name, $phone, $time->format('Y-m-d H:i:s'),
                 $guests, $notes !== '' ? $notes : null, self::CHO_XAC_NHAN]);
            DB::exec("INSERT INTO datban_lichsu
                (datban_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                VALUES (?,NULL,?,?,?)",
                [$bookingId, self::CHO_XAC_NHAN, $userId ? (int)$userId : null, 'Khách gửi yêu cầu đặt bàn']);
            $pdo->commit();
            return $bookingId;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function danhSach($branchId = null, $status = null, $limit = 200) {
        $sql = "SELECT b.*, c.tenchinhanh, h.ghichu AS lichsu_moi, h.ngaytao AS lichsu_ngaytao,
                       u.hoten AS lichsu_nguoi
                FROM datban b JOIN chinhanh c ON c.id = b.chinhanh_id
                LEFT JOIN datban_lichsu h ON h.id = (
                    SELECT MAX(h2.id) FROM datban_lichsu h2 WHERE h2.datban_id = b.id
                )
                LEFT JOIN nguoidung u ON u.id = h.nguoidung_id
                WHERE 1=1";
        $params = [];
        if ($branchId) { $sql .= " AND b.chinhanh_id = ?"; $params[] = (int)$branchId; }
        if ($status !== null && $status !== '') { $sql .= " AND b.trangthai = ?"; $params[] = (int)$status; }
        $sql .= " ORDER BY b.thoi_gian ASC, b.id ASC LIMIT " . max(1, (int)$limit);
        return DB::all($sql, $params);
    }

    public static function cuaNguoiDung($userId) {
        return DB::all("SELECT b.*, c.tenchinhanh, h.ghichu AS lichsu_moi, h.ngaytao AS lichsu_ngaytao
                        FROM datban b JOIN chinhanh c ON c.id = b.chinhanh_id
                        LEFT JOIN datban_lichsu h ON h.id = (
                            SELECT MAX(h2.id) FROM datban_lichsu h2 WHERE h2.datban_id = b.id
                        )
                        WHERE b.nguoidung_id = ? ORDER BY b.thoi_gian DESC, b.id DESC", [(int)$userId]);
    }

    public static function capNhatTrangThai($id, $newStatus, $branchLimit = null, $actorId = null) {
        if (filter_var($newStatus, FILTER_VALIDATE_INT) === false) {
            throw new RuntimeException('Trạng thái đặt bàn không hợp lệ.');
        }
        $newStatus = (int)$newStatus;
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $booking = DB::one("SELECT * FROM datban WHERE id = ? FOR UPDATE", [(int)$id]);
            if (!$booking) throw new RuntimeException('Không tìm thấy yêu cầu đặt bàn.');
            if ($branchLimit !== null && (int)$booking['chinhanh_id'] !== (int)$branchLimit) {
                throw new RuntimeException('Bạn không có quyền cập nhật đặt bàn của chi nhánh khác.');
            }

            $current = (int)$booking['trangthai'];
            $allowed = [
                self::CHO_XAC_NHAN => [self::DA_XAC_NHAN, self::DA_HUY, self::TU_CHOI],
                self::DA_XAC_NHAN => [self::DA_DEN, self::DA_HUY, self::KHONG_DEN],
            ];
            if (!in_array((int)$newStatus, $allowed[$current] ?? [], true)) {
                throw new RuntimeException('Không thể chuyển yêu cầu đặt bàn sang trạng thái này.');
            }
            if ((int)$newStatus === self::DA_XAC_NHAN) {
                if (strtotime($booking['thoi_gian']) <= time()) {
                    throw new RuntimeException('Không thể xác nhận đặt bàn có thời gian trong quá khứ.');
                }
                $branch = DB::one("SELECT suc_chua, thoi_luong_datban_phut FROM chinhanh
                                   WHERE id = ? FOR UPDATE", [(int)$booking['chinhanh_id']]);
                if (!$branch) throw new RuntimeException('Không tìm thấy cấu hình chi nhánh.');
                $start = new DateTimeImmutable($booking['thoi_gian']);
                $end = $start->modify('+' . (int)$branch['thoi_luong_datban_phut'] . ' minutes');
                $reservations = DB::all(
                    "SELECT thoi_gian, so_khach FROM datban
                     WHERE chinhanh_id = ? AND trangthai = ? AND id <> ?
                       AND thoi_gian < ? AND DATE_ADD(thoi_gian, INTERVAL ? MINUTE) > ?",
                    [(int)$booking['chinhanh_id'], self::DA_XAC_NHAN, (int)$id,
                     $end->format('Y-m-d H:i:s'), (int)$branch['thoi_luong_datban_phut'],
                     $start->format('Y-m-d H:i:s')]);
                $events = [
                    ['time' => $start->getTimestamp(), 'delta' => (int)$booking['so_khach']],
                    ['time' => $end->getTimestamp(), 'delta' => -(int)$booking['so_khach']],
                ];
                foreach ($reservations as $reservation) {
                    $reservationStart = new DateTimeImmutable($reservation['thoi_gian']);
                    $reservationEnd = $reservationStart->modify('+' . (int)$branch['thoi_luong_datban_phut'] . ' minutes');
                    $events[] = [
                        'time' => max($start->getTimestamp(), $reservationStart->getTimestamp()),
                        'delta' => (int)$reservation['so_khach'],
                    ];
                    $events[] = [
                        'time' => min($end->getTimestamp(), $reservationEnd->getTimestamp()),
                        'delta' => -(int)$reservation['so_khach'],
                    ];
                }
                usort($events, function ($a, $b) {
                    return ($a['time'] <=> $b['time']) ?: ($a['delta'] <=> $b['delta']);
                });
                $currentGuests = 0;
                $peakGuests = 0;
                foreach ($events as $event) {
                    $currentGuests += $event['delta'];
                    $peakGuests = max($peakGuests, $currentGuests);
                }
                if ($peakGuests > (int)$branch['suc_chua']) {
                    throw new RuntimeException('Khung giờ này đã vượt sức chứa chi nhánh. Hãy chọn khung giờ khác hoặc điều chỉnh số khách.');
                }
            }
            DB::exec("UPDATE datban SET trangthai = ? WHERE id = ?",
                [(int)$newStatus, (int)$id]);
            DB::exec("INSERT INTO datban_lichsu
                (datban_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                VALUES (?,?,?,?,?)",
                [(int)$id, $current, (int)$newStatus, $actorId ? (int)$actorId : null,
                 'Nhà hàng cập nhật trạng thái đặt bàn']);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function khachHuy($id, $userId) {
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $booking = DB::one("SELECT * FROM datban WHERE id = ? AND nguoidung_id = ? FOR UPDATE",
                [(int)$id, (int)$userId]);
            if (!$booking) throw new RuntimeException('Không tìm thấy yêu cầu đặt bàn của bạn.');
            if ((int)$booking['trangthai'] !== self::CHO_XAC_NHAN) {
                throw new RuntimeException('Chỉ có thể hủy yêu cầu đang chờ nhà hàng xác nhận.');
            }
            DB::exec("UPDATE datban SET trangthai = ? WHERE id = ?",
                [self::DA_HUY, (int)$id]);
            DB::exec("INSERT INTO datban_lichsu
                (datban_id, trangthai_cu, trangthai_moi, nguoidung_id, ghichu)
                VALUES (?,?,?,?,?)",
                [(int)$id, self::CHO_XAC_NHAN, self::DA_HUY, (int)$userId, 'Khách hủy yêu cầu đặt bàn']);
            $pdo->commit();
            return true;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function nhanTrangThai($status) {
        $labels = [
            self::CHO_XAC_NHAN => ['Chờ xác nhận', 'warning'],
            self::DA_XAC_NHAN => ['Đã xác nhận', 'primary'],
            self::DA_DEN => ['Đã đến', 'success'],
            self::DA_HUY => ['Đã hủy', 'secondary'],
            self::KHONG_DEN => ['Không đến', 'dark'],
            self::TU_CHOI => ['Từ chối', 'danger'],
        ];
        return $labels[(int)$status] ?? ['Không rõ', 'dark'];
    }

    private static function parseDate($value) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors && ($errors['warning_count'] || $errors['error_count']))) return null;
        return $date;
    }
}
