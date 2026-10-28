<?php
class Slider {
    private static function ensureTable() {
        try {
            DB::exec("CREATE TABLE IF NOT EXISTS slider (
                id INT AUTO_INCREMENT PRIMARY KEY,
                tieu_de VARCHAR(255) NOT NULL,
                mota TEXT,
                hinh_anh VARCHAR(255) NOT NULL,
                nut_text VARCHAR(100) DEFAULT NULL,
                nut_link VARCHAR(255) DEFAULT '#',
                vitri INT NOT NULL DEFAULT 0,
                trangthai TINYINT NOT NULL DEFAULT 1
            ) ENGINE=InnoDB");
        } catch (Throwable $e) {
        }
    }

    public static function tatCa() {
        self::ensureTable();
        try {
            return DB::all("SELECT * FROM slider WHERE trangthai = 1 ORDER BY vitri, id");
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function tatCaAdmin() {
        self::ensureTable();
        try {
            return DB::all("SELECT * FROM slider ORDER BY vitri, id");
        } catch (Throwable $e) {
            return [];
        }
    }

    public static function laySo($id) {
        self::ensureTable();
        try {
            return DB::one("SELECT * FROM slider WHERE id = ?", [(int)$id]);
        } catch (Throwable $e) {
            return null;
        }
    }

    public static function them($tieuDe, $mota, $hinhAnh, $nutText, $nutLink, $vitri, $trangThai = 1) {
        self::ensureTable();
        return DB::insert(
            "INSERT INTO slider (tieu_de, mota, hinh_anh, nut_text, nut_link, vitri, trangthai) VALUES (?,?,?,?,?,?,?)",
            [$tieuDe, $mota, $hinhAnh, $nutText, $nutLink, (int)$vitri, (int)$trangThai]
        );
    }

    public static function sua($id, $tieuDe, $mota, $hinhAnh, $nutText, $nutLink, $vitri, $trangThai = 1) {
        self::ensureTable();
        return DB::exec(
            "UPDATE slider SET tieu_de=?, mota=?, hinh_anh=?, nut_text=?, nut_link=?, vitri=?, trangthai=? WHERE id=?",
            [$tieuDe, $mota, $hinhAnh, $nutText, $nutLink, (int)$vitri, (int)$trangThai, (int)$id]
        );
    }

    public static function xoa($id) {
        self::ensureTable();
        try {
            $r = DB::one("SELECT hinh_anh FROM slider WHERE id=?", [(int)$id]);
            if ($r && !preg_match('/^https?:\/\//i', (string)$r['hinh_anh'])) {
                $file = ROOT . '/uploads/slider/' . basename((string)$r['hinh_anh']);
                if (is_file($file)) unlink($file);
            }
            return DB::exec("DELETE FROM slider WHERE id=?", [(int)$id]);
        } catch (Throwable $e) {
            return 0;
        }
    }

    public static function doiTrangThai($id) {
        self::ensureTable();
        try {
            return DB::exec("UPDATE slider SET trangthai = 1 - trangthai WHERE id=?", [(int)$id]);
        } catch (Throwable $e) {
            return 0;
        }
    }
}
