<?php
class Voucher {
    public static function tatCa() {
        return DB::all("SELECT v.*, c.tenchinhanh FROM voucher v
                        LEFT JOIN chinhanh c ON c.id = v.chinhanh_id
                        ORDER BY v.id DESC");
    }

    public static function laySo($id) {
        return DB::one("SELECT * FROM voucher WHERE id = ?", [(int)$id]);
    }

    public static function luu(array $data) {
        $id = (int)($data['id'] ?? 0);
        $code = strtoupper(trim((string)($data['ma'] ?? '')));
        $type = (string)($data['loai'] ?? '');
        $value = (float)($data['giatri'] ?? 0);
        $minimum = (float)($data['don_toi_thieu'] ?? 0);
        $maximum = ($data['giam_toi_da'] ?? '') === '' ? null : (float)$data['giam_toi_da'];
        $start = self::parseDate((string)($data['bat_dau'] ?? ''));
        $end = self::parseDate((string)($data['ket_thuc'] ?? ''));
        if ($end) $end = $end->modify('+59 seconds');
        $limit = ($data['gioi_han'] ?? '') === '' ? null : (int)$data['gioi_han'];
        $branch = (int)($data['chinhanh_id'] ?? 0);

        if (!preg_match('/^[A-Z0-9_-]{3,40}$/', $code)) {
            throw new RuntimeException('Mã voucher phải dài 3–40 ký tự, chỉ gồm chữ, số, gạch ngang hoặc gạch dưới.');
        }
        if (!in_array($type, ['phantram', 'tienmat'], true)) {
            throw new RuntimeException('Loại giảm giá không hợp lệ.');
        }
        if ($value <= 0 || ($type === 'phantram' && $value > 100)) {
            throw new RuntimeException($type === 'phantram' ? 'Phần trăm giảm phải lớn hơn 0 và không vượt quá 100%.' : 'Số tiền giảm phải lớn hơn 0.');
        }
        if ($minimum < 0 || ($maximum !== null && ($maximum <= 0 || $type !== 'phantram'))) {
            throw new RuntimeException('Giá trị đơn tối thiểu hoặc mức giảm tối đa không hợp lệ.');
        }
        if (!$start || !$end || $end <= $start) {
            throw new RuntimeException('Thời gian kết thúc phải sau thời gian bắt đầu.');
        }
        if ($limit !== null && $limit < 1) {
            throw new RuntimeException('Giới hạn lượt dùng phải lớn hơn 0 hoặc để trống nếu không giới hạn.');
        }
        if ($branch && !ChiNhanh::laySo($branch)) {
            throw new RuntimeException('Chi nhánh áp dụng không hợp lệ.');
        }

        $existing = $id ? self::laySo($id) : null;
        if ($id && !$existing) throw new RuntimeException('Không tìm thấy voucher cần cập nhật.');
        if ($limit !== null && $existing && $limit < (int)$existing['da_dung']) {
            throw new RuntimeException('Giới hạn lượt dùng không được nhỏ hơn số lượt đã sử dụng.');
        }
        $duplicate = DB::one("SELECT id FROM voucher WHERE ma = ? AND id <> ?", [$code, $id]);
        if ($duplicate) throw new RuntimeException('Mã voucher đã tồn tại.');

        $params = [$code, $type, $value, $minimum, $maximum, $start->format('Y-m-d H:i:s'),
                   $end->format('Y-m-d H:i:s'), $limit, $branch ?: null, !empty($data['trangthai']) ? 1 : 0];
        if ($id) {
            DB::exec("UPDATE voucher SET ma=?, loai=?, giatri=?, don_toi_thieu=?, giam_toi_da=?,
                      bat_dau=?, ket_thuc=?, gioi_han=?, chinhanh_id=?, trangthai=? WHERE id=?",
                array_merge($params, [$id]));
            return $id;
        }
        return DB::insert("INSERT INTO voucher
            (ma, loai, giatri, don_toi_thieu, giam_toi_da, bat_dau, ket_thuc, gioi_han, chinhanh_id, trangthai)
            VALUES (?,?,?,?,?,?,?,?,?,?)", $params);
    }

    public static function doiTrangThai($id) {
        return DB::exec("UPDATE voucher SET trangthai = 1 - trangthai WHERE id = ?", [(int)$id]);
    }

    public static function tinhGiam($code, $branchId, $subtotal, $lock = false) {
        $code = strtoupper(trim((string)$code));
        if ($code === '') return ['voucher' => null, 'giamgia' => 0];

        $sql = "SELECT * FROM voucher WHERE ma = ?";
        if ($lock) $sql .= " FOR UPDATE";
        $voucher = DB::one($sql, [$code]);
        if (!$voucher) throw new RuntimeException('Mã voucher không tồn tại.');
        if (!(int)$voucher['trangthai']) throw new RuntimeException('Voucher hiện không hoạt động.');

        $now = new DateTimeImmutable('now');
        $start = new DateTimeImmutable($voucher['bat_dau']);
        $end = new DateTimeImmutable($voucher['ket_thuc']);
        if ($now < $start || $now > $end) throw new RuntimeException('Voucher chưa bắt đầu hoặc đã hết hạn.');
        if ($voucher['chinhanh_id'] !== null && (int)$voucher['chinhanh_id'] !== (int)$branchId) {
            throw new RuntimeException('Voucher không áp dụng tại chi nhánh đã chọn.');
        }
        if ((float)$subtotal < (float)$voucher['don_toi_thieu']) {
            throw new RuntimeException('Đơn hàng chưa đạt giá trị tối thiểu để dùng voucher.');
        }
        if ($voucher['gioi_han'] !== null && (int)$voucher['da_dung'] >= (int)$voucher['gioi_han']) {
            throw new RuntimeException('Voucher đã hết lượt sử dụng.');
        }

        $discount = $voucher['loai'] === 'phantram'
            ? (int)floor((float)$subtotal * (float)$voucher['giatri'] / 100)
            : (int)round((float)$voucher['giatri']);
        if ($voucher['giam_toi_da'] !== null) {
            $discount = min($discount, (int)round((float)$voucher['giam_toi_da']));
        }
        $discount = min(max(0, $discount), (int)round((float)$subtotal));
        return ['voucher' => $voucher, 'giamgia' => $discount];
    }

    public static function danhDauDaDung($id) {
        return DB::exec("UPDATE voucher SET da_dung = da_dung + 1
                         WHERE id = ? AND (gioi_han IS NULL OR da_dung < gioi_han)", [(int)$id]);
    }

    public static function hoanLuotSuDung($id) {
        return DB::exec("UPDATE voucher SET da_dung = da_dung - 1
                         WHERE id = ? AND da_dung > 0", [(int)$id]);
    }

    private static function parseDate($value) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors && ($errors['warning_count'] || $errors['error_count']))) return null;
        return $date;
    }
}
