<?php
class NhomMon {
    public static function tatCa() { return DB::all("SELECT * FROM nhomon ORDER BY id"); }
    public static function laySo($id) { return DB::one("SELECT * FROM nhomon WHERE id=?", [(int)$id]); }
    public static function them($ten) { return DB::insert("INSERT INTO nhomon (tennhom) VALUES (?)", [$ten]); }
    public static function sua($id, $ten) { return DB::exec("UPDATE nhomon SET tennhom=? WHERE id=?", [$ten, (int)$id]); }
    public static function demMon($id) { return (int)DB::val("SELECT COUNT(*) FROM monan WHERE nhom_id=?", [(int)$id]); }
    public static function xoa($id) {
        if (self::demMon($id) > 0) return false;   // không xóa nhóm còn món
        return DB::exec("DELETE FROM nhomon WHERE id=?", [(int)$id]) > 0;
    }
}
