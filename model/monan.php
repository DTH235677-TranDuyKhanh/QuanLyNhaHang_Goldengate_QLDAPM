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
    public static function capNhatTon($cn, $monId, $sl, $phucvu) {
        return DB::exec("UPDATE monan_chinhanh SET soluongton = ?, conphucvu = ? WHERE chinhanh_id = ? AND monan_id = ?",
            [max(0, (int)$sl), $phucvu ? 1 : 0, (int)$cn, (int)$monId]);
    }
}
