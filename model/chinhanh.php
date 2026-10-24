<?php
class ChiNhanh {
    public static function dangHoatDong() { return DB::all("SELECT * FROM chinhanh WHERE trangthai=1 ORDER BY id"); }
    public static function tatCa() { return DB::all("SELECT * FROM chinhanh ORDER BY id"); }
    public static function laySo($id) { return DB::one("SELECT * FROM chinhanh WHERE id=?", [(int)$id]); }

    public static function them($ten, $diachi, $sdt, $mo, $dong) {
        $id = DB::insert("INSERT INTO chinhanh (tenchinhanh,diachi,sodienthoai,giomocua,giodongcua) VALUES (?,?,?,?,?)",
            [$ten, $diachi, $sdt, $mo, $dong]);
        // Chi nhánh mới: tạo sẵn dòng tồn kho = 0 cho mọi món
        DB::exec("INSERT INTO monan_chinhanh (chinhanh_id, monan_id, soluongton, conphucvu) SELECT ?, id, 0, 1 FROM monan", [$id]);
        return $id;
    }  
    public static function sua($id, $ten, $diachi, $sdt, $mo, $dong) {
        return DB::exec("UPDATE chinhanh SET tenchinhanh=?, diachi=?, sodienthoai=?, giomocua=?, giodongcua=? WHERE id=?",
            [$ten, $diachi, $sdt, $mo, $dong, (int)$id]);
    }
    public static function doiTrangThai($id) { return DB::exec("UPDATE chinhanh SET trangthai = 1 - trangthai WHERE id=?", [(int)$id]); }
}
