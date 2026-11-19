<?php
class MonAn {
    // Truy vấn món kèm tồn kho tại 1 chi nhánh (tham số đầu tiên luôn là chi nhánh)
    const SEL = "SELECT m.*, n.tennhom, mc.soluongton, mc.conphucvu
                 FROM monan m JOIN nhomon n ON n.id = m.nhom_id
                 LEFT JOIN monan_chinhanh mc ON mc.monan_id = m.id AND mc.chinhanh_id = ? ";

    public static function layTheoChiNhanh($cn, $nhom = null, $q = null) {
        $sql = self::SEL . " WHERE m.trangthai = 1 AND mc.conphucvu = 1";
        $p = [(int)$cn];
        if ($nhom) { $sql .= " AND m.nhom_id = ?"; $p[] = (int)$nhom; }
        if ($q !== null && $q !== '') { $sql .= " AND m.tenmon LIKE ?"; $p[] = '%' . $q . '%'; }
        $sql .= " ORDER BY m.nhom_id, m.luotgoi DESC, m.id";
        return DB::all($sql, $p);
    }
    public static function layTheoId($id, $cn) {
        return DB::one(self::SEL . " WHERE m.id = ?", [(int)$cn, (int)$id]);
    }
    public static function layTheoIds($ids, $cn) {
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) return [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        return DB::all(self::SEL . " WHERE m.trangthai = 1 AND mc.conphucvu = 1 AND m.id IN ($in)", array_merge([(int)$cn], $ids));
    }
    public static function banChay($cn, $limit = 4) {
        $limit = max(1, (int)$limit);
        return DB::all(self::SEL . " WHERE m.trangthai = 1 AND mc.conphucvu = 1 AND mc.soluongton > 0
                                    ORDER BY m.luotgoi DESC, m.id LIMIT $limit", [(int)$cn]);
    }
    public static function tangLuotXem($id) { DB::exec("UPDATE monan SET luotxem = luotxem + 1 WHERE id = ?", [(int)$id]); }

    // ---------- Quản trị ----------
    public static function tatCa() {
        return DB::all("SELECT m.*, n.tennhom FROM monan m JOIN nhomon n ON n.id = m.nhom_id ORDER BY m.nhom_id, m.id");
    }
    public static function laySo($id) { return DB::one("SELECT * FROM monan WHERE id = ?", [(int)$id]); }

    public static function them($ten, $mota, $gia, $nhom, $hinh) {
        $id = DB::insert("INSERT INTO monan (tenmon, mota, giaban, nhom_id, hinhanh) VALUES (?,?,?,?,?)",
            [$ten, $mota, $gia, (int)$nhom, $hinh]);
        // Mỗi chi nhánh có 1 dòng tồn kho cho món mới (mặc định 0 phần)
        DB::exec("INSERT INTO monan_chinhanh (chinhanh_id, monan_id, soluongton, conphucvu) SELECT id, ?, 0, 1 FROM chinhanh", [$id]);
        return $id;
    }
    public static function sua($id, $ten, $mota, $gia, $nhom, $hinh = null) {
        if ($hinh) {
            $cur = self::laySo((int)$id);
            if ($cur && !empty($cur['hinhanh']) && $cur['hinhanh'] !== $hinh && !preg_match('/^https?:\/\//i', (string)$cur['hinhanh'])) {
                $oldFile = ROOT . '/uploads/monan/' . basename((string)$cur['hinhanh']);
                if (is_file($oldFile)) unlink($oldFile);
            }
            return DB::exec("UPDATE monan SET tenmon=?, mota=?, giaban=?, nhom_id=?, hinhanh=? WHERE id=?",
                [$ten, $mota, $gia, (int)$nhom, $hinh, (int)$id]);
        }
        return DB::exec("UPDATE monan SET tenmon=?, mota=?, giaban=?, nhom_id=? WHERE id=?",
            [$ten, $mota, $gia, (int)$nhom, (int)$id]);
    }
    public static function doiTrangThai($id) { return DB::exec("UPDATE monan SET trangthai = 1 - trangthai WHERE id = ?", [(int)$id]); }

    public static function tonTheoChiNhanh($cn) {
        return DB::all("SELECT m.id, m.tenmon, m.giaban, n.tennhom, mc.soluongton, mc.conphucvu
                        FROM monan m JOIN nhomon n ON n.id = m.nhom_id
                        JOIN monan_chinhanh mc ON mc.monan_id = m.id AND mc.chinhanh_id = ?
                        WHERE m.trangthai = 1 ORDER BY m.nhom_id, m.id", [(int)$cn]);
    }
    public static function capNhatTon($cn, $monId, $sl, $phucvu, $userId = null) {
        $cn = (int)$cn;
        $monId = (int)$monId;
        $deltas = self::capNhatTonNhieu($cn, [
            $monId => ['soluong' => $sl, 'phucvu' => $phucvu],
        ], $userId);
        return $deltas[$monId];
    }

    public static function capNhatTonNhieu($cn, array $updates, $userId = null) {
        if (!$updates) return [];
        ksort($updates, SORT_NUMERIC);
        $validated = [];
        foreach ($updates as $monId => $update) {
            if (!is_array($update)) throw new RuntimeException('Dữ liệu tồn kho không hợp lệ.');
            $monId = filter_var($monId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            $quantity = filter_var($update['soluong'] ?? null, FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 0, 'max_range' => 2147483647],
            ]);
            if ($monId === false || $quantity === false) {
                throw new RuntimeException('Số lượng tồn phải là số nguyên không âm.');
            }
            $validated[(int)$monId] = ['soluong' => $quantity, 'phucvu' => !empty($update['phucvu'])];
        }

        $cn = (int)$cn;
        $pdo = DB::pdo();
        $pdo->beginTransaction();
        try {
            $deltas = [];
            foreach ($validated as $monId => $update) {
                $current = DB::one("SELECT soluongton FROM monan_chinhanh
                                    WHERE chinhanh_id = ? AND monan_id = ? FOR UPDATE", [$cn, $monId]);
                if (!$current) throw new RuntimeException('Không tìm thấy món trong tồn kho chi nhánh.');
                $delta = $update['soluong'] - (int)$current['soluongton'];
                DB::exec("UPDATE monan_chinhanh SET soluongton = ?, conphucvu = ?
                          WHERE chinhanh_id = ? AND monan_id = ?",
                    [$update['soluong'], $update['phucvu'] ? 1 : 0, $cn, $monId]);
                if ($delta !== 0) {
                    DB::exec("INSERT INTO tonkho_lichsu
                        (chinhanh_id, monan_id, bien_dong, loai, tham_chieu, ghi_chu, nguoidung_id)
                        VALUES (?,?,?,?,?,?,?)",
                        [$cn, $monId, $delta, $delta > 0 ? 'nhap_hang' : 'dieu_chinh',
                         null, 'Cập nhật tồn kho thủ công', $userId ? (int)$userId : null]);
                }
                $deltas[$monId] = $delta;
            }
            $pdo->commit();
            return $deltas;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            throw $e;
        }
    }

    public static function lichSuTon($branchId, $limit = 100) {
        $limit = max(1, min(500, (int)$limit));
        return DB::all("SELECT l.*, c.tenchinhanh, m.tenmon, u.hoten
                        FROM tonkho_lichsu l
                        JOIN chinhanh c ON c.id = l.chinhanh_id
                        JOIN monan m ON m.id = l.monan_id
                        LEFT JOIN nguoidung u ON u.id = l.nguoidung_id
                        WHERE l.chinhanh_id = ?
                        ORDER BY l.id DESC LIMIT " . $limit, [(int)$branchId]);
    }
}
