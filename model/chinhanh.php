<?php
class ChiNhanh {
    public static function dangHoatDong() { return DB::all("SELECT * FROM chinhanh WHERE trangthai=1 ORDER BY id"); }
    public static function tatCa() { return DB::all("SELECT * FROM chinhanh ORDER BY id"); }
    public static function laySo($id) { return DB::one("SELECT * FROM chinhanh WHERE id=?", [(int)$id]); }

    public static function them($ten, $diachi, $sdt, $mo, $dong, $capacity = 60, $bookingDuration = 120) {
        self::validateBookingSettings($capacity, $bookingDuration);
        $id = DB::insert("INSERT INTO chinhanh
            (tenchinhanh,diachi,sodienthoai,giomocua,giodongcua,suc_chua,thoi_luong_datban_phut)
            VALUES (?,?,?,?,?,?,?)",
            [$ten, $diachi, $sdt, $mo, $dong, (int)$capacity, (int)$bookingDuration]);
        // Chi nhánh mới: tạo sẵn dòng tồn kho = 0 cho mọi món
        DB::exec("INSERT INTO monan_chinhanh (chinhanh_id, monan_id, soluongton, conphucvu) SELECT ?, id, 0, 1 FROM monan", [$id]);
        return $id;
    }
    public static function sua($id, $ten, $diachi, $sdt, $mo, $dong, $capacity = 60, $bookingDuration = 120) {
        self::validateBookingSettings($capacity, $bookingDuration);
        return DB::exec("UPDATE chinhanh SET tenchinhanh=?, diachi=?, sodienthoai=?, giomocua=?, giodongcua=?,
                         suc_chua=?, thoi_luong_datban_phut=? WHERE id=?",
            [$ten, $diachi, $sdt, $mo, $dong, (int)$capacity, (int)$bookingDuration, (int)$id]);
    }
    public static function doiTrangThai($id) { return DB::exec("UPDATE chinhanh SET trangthai = 1 - trangthai WHERE id=?", [(int)$id]); }

    private static function validateBookingSettings($capacity, $duration) {
        $capacity = filter_var($capacity, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1, 'max_range' => 10000]]);
        if ($capacity === false) {
            throw new RuntimeException('Sức chứa phải nằm trong khoảng 1 đến 10.000 khách.');
        }
        $duration = filter_var($duration, FILTER_VALIDATE_INT, ['options' => ['min_range' => 30, 'max_range' => 480]]);
        if ($duration === false) {
            throw new RuntimeException('Thời lượng đặt bàn phải từ 30 đến 480 phút.');
        }
    }
}
