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
        $guests = (int)($data['so_khach'] ?? 0);
        $notes = trim((string)($data['ghichu'] ?? ''));
        $time = self::parseDate((string)($data['thoi_gian'] ?? ''));

        if ($name === '' || strlen($name) > 255) throw new RuntimeException('Vui lòng nhập họ tên hợp lệ.');
        if (!preg_match('/^0\d{9}$/', $phone)) throw new RuntimeException('Số điện thoại phải gồm 10 chữ số, bắt đầu bằng 0.');
        if ($guests < 1 || $guests > 30) throw new RuntimeException('Số khách phải từ 1 đến 30.');
        if (!$time || $time <= new DateTimeImmutable('now')) throw new RuntimeException('Vui lòng chọn thời gian đặt bàn trong tương lai.');
        if (strlen($notes) > 500) throw new RuntimeException('Ghi chú không được vượt quá 500 ký tự.');

        $open = substr((string)$branch['giomocua'], 0, 5);
        $close = substr((string)$branch['giodongcua'], 0, 5);
        $at = $time->format('H:i');
        $withinHours = $close >= $open
            ? ($at >= $open && $at < $close)
            : ($at >= $open || $at < $close);
        if (!$withinHours) {
            throw new RuntimeException('Thời gian đặt bàn nằm ngoài giờ hoạt động của chi nhánh (' . $open . '–' . $close . ').');
        }

        return DB::insert("INSERT INTO datban
            (nguoidung_id, chinhanh_id, hoten, sodienthoai, thoi_gian, so_khach, ghichu, trangthai)
            VALUES (?,?,?,?,?,?,?,?)",
            [$userId ? (int)$userId : null, $branchId, $name, $phone, $time->format('Y-m-d H:i:s'),
             $guests, $notes !== '' ? $notes : null, self::CHO_XAC_NHAN]);
    }

    public static function danhSach($branchId = null, $status = null, $limit = 200) {
        $sql = "SELECT b.*, c.tenchinhanh FROM datban b JOIN chinhanh c ON c.id = b.chinhanh_id WHERE 1=1";
        $params = [];
        if ($branchId) { $sql .= " AND b.chinhanh_id = ?"; $params[] = (int)$branchId; }
        if ($status !== null && $status !== '') { $sql .= " AND b.trangthai = ?"; $params[] = (int)$status; }
        $sql .= " ORDER BY b.thoi_gian ASC, b.id ASC LIMIT " . max(1, (int)$limit);
        return DB::all($sql, $params);
    }

    public static function cuaNguoiDung($userId) {
        return DB::all("SELECT b.*, c.tenchinhanh FROM datban b JOIN chinhanh c ON c.id = b.chinhanh_id
                        WHERE b.nguoidung_id = ? ORDER BY b.thoi_gian DESC, b.id DESC", [(int)$userId]);
    }

    public static function capNhatTrangThai($id, $newStatus, $branchLimit = null) {
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
            DB::exec("UPDATE datban SET trangthai = ? WHERE id = ?",
                [(int)$newStatus, (int)$id]);
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
