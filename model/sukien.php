<?php
class SuKien {
    public static function tatCaAdmin() {
        return DB::all("SELECT s.*, c.tenchinhanh, v.ma AS ma_uu_dai_hienthi
                        FROM sukien s
                        LEFT JOIN chinhanh c ON c.id = s.chinhanh_id
                        LEFT JOIN voucher v ON v.id = s.voucher_id
                        ORDER BY s.bat_dau DESC, s.id DESC");
    }

    public static function laySo($id) {
        return DB::one("SELECT s.*, v.ma AS ma_uu_dai FROM sukien s
                        LEFT JOIN voucher v ON v.id = s.voucher_id WHERE s.id = ?", [(int)$id]);
    }

    public static function dangDienRa($branchId, $limit = 50) {
        $limit = max(1, min(100, (int)$limit));
        return DB::all("SELECT s.*, c.tenchinhanh,
                               CASE WHEN v.trangthai = 1 AND v.bat_dau <= NOW() AND v.ket_thuc >= NOW()
                                    AND (v.chinhanh_id IS NULL OR v.chinhanh_id = ?)
                                    THEN v.ma ELSE NULL END AS ma_uu_dai_hienthi
                        FROM sukien s
                        LEFT JOIN chinhanh c ON c.id = s.chinhanh_id
                        LEFT JOIN voucher v ON v.id = s.voucher_id
                        WHERE s.trangthai = 1
                          AND s.bat_dau <= NOW()
                          AND s.ket_thuc >= NOW()
                          AND (s.chinhanh_id IS NULL OR s.chinhanh_id = ?)
                        ORDER BY s.bat_dau DESC, s.id DESC
                        LIMIT $limit", [(int)$branchId, (int)$branchId]);
    }

    public static function luu(array $data, $image) {
        $id = (int)($data['id'] ?? 0);
        $title = trim((string)($data['tieu_de'] ?? ''));
        $description = trim((string)($data['mo_ta'] ?? ''));
        $voucherId = (int)($data['voucher_id'] ?? 0);
        $start = self::parseDate((string)($data['bat_dau'] ?? ''));
        $end = self::parseDate((string)($data['ket_thuc'] ?? ''));
        $branchId = (int)($data['chinhanh_id'] ?? 0);
        $enabled = !empty($data['trangthai']) ? 1 : 0;

        $titleLength = preg_match_all('/./us', $title, $matches);
        if ($title === '' || !preg_match('//u', $title) || $titleLength === false || $titleLength > 255) {
            throw new RuntimeException('Tiêu đề sự kiện là bắt buộc và không được vượt quá 255 ký tự.');
        }
        if ($description === '') {
            throw new RuntimeException('Vui lòng nhập nội dung sự kiện.');
        }
        if (!$start || !$end || $end <= $start) {
            throw new RuntimeException('Thời gian kết thúc phải sau thời gian bắt đầu.');
        }
        if ($branchId && !ChiNhanh::laySo($branchId)) {
            throw new RuntimeException('Chi nhánh áp dụng không hợp lệ.');
        }
        $voucher = $voucherId ? Voucher::laySo($voucherId) : null;
        if ($voucherId && !$voucher) throw new RuntimeException('Voucher liên kết không tồn tại.');
        if ($voucher && !(int)$voucher['trangthai']) throw new RuntimeException('Chỉ có thể liên kết sự kiện với voucher đang hoạt động.');
        if ($voucher && ($start < new DateTimeImmutable($voucher['bat_dau'])
            || $end > new DateTimeImmutable($voucher['ket_thuc']))) {
            throw new RuntimeException('Thời gian sự kiện phải nằm trong thời hạn của voucher.');
        }
        if ($voucher && $voucher['chinhanh_id'] !== null
            && (!$branchId || (int)$voucher['chinhanh_id'] !== $branchId)) {
            throw new RuntimeException('Sự kiện phải giới hạn đúng chi nhánh của voucher đã chọn.');
        }

        $current = $id ? self::laySo($id) : null;
        if ($id && !$current) {
            throw new RuntimeException('Không tìm thấy sự kiện cần cập nhật.');
        }
        $image = $image ?: ($current['hinh_anh'] ?? null);
        if (!$image) {
            throw new RuntimeException('Vui lòng chọn ảnh cho sự kiện.');
        }

        $values = [
            $title, $description, $image, $voucher ? $voucher['ma'] : null, $voucher ? (int)$voucher['id'] : null,
            $start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s'),
            $branchId ?: null, $enabled,
        ];
        if ($id) {
            DB::exec("UPDATE sukien SET tieu_de=?, mo_ta=?, hinh_anh=?, ma_uu_dai=?, voucher_id=?,
                      bat_dau=?, ket_thuc=?, chinhanh_id=?, trangthai=? WHERE id=?",
                array_merge($values, [$id]));
            return $id;
        }
        return DB::insert("INSERT INTO sukien
            (tieu_de, mo_ta, hinh_anh, ma_uu_dai, voucher_id, bat_dau, ket_thuc, chinhanh_id, trangthai)
            VALUES (?,?,?,?,?,?,?,?,?)", $values);
    }

    public static function doiTrangThai($id) {
        return DB::exec("UPDATE sukien SET trangthai = 1 - trangthai WHERE id = ?", [(int)$id]);
    }

    private static function parseDate($value) {
        $date = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $value);
        $errors = DateTimeImmutable::getLastErrors();
        if (!$date || ($errors && ($errors['warning_count'] || $errors['error_count']))) return null;
        return $date;
    }
}
