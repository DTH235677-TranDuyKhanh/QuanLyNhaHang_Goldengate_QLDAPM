<?php
class ThongKe {
    // $cn = null: toàn hệ thống; có giá trị: chỉ chi nhánh đó
    public static function tongQuan($cn = null) {
        $w = $cn ? " AND chinhanh_id = " . (int)$cn : "";
        $wm = $cn ? " AND chinhanh_id = " . (int)$cn : "";
        return [
            'don_homnay'      => (int)DB::val("SELECT COUNT(*) FROM donhang WHERE DATE(ngay) = CURDATE() $w"),
            'doanhthu_homnay' => (float)DB::val("SELECT COALESCE(SUM(tongtien),0) FROM donhang WHERE DATE(ngay) = CURDATE() AND trangthai <> 4 $w"),
            'don_cho'         => (int)DB::val("SELECT COUNT(*) FROM donhang WHERE trangthai = 0 $w"),
            'sap_het'         => (int)DB::val("SELECT COUNT(*) FROM monan_chinhanh WHERE conphucvu = 1 AND soluongton <= 5 $wm"),
        ];
    }
    public static function doanhThuTheoChiNhanh($days = 30) {
        $days = max(1, (int)$days);
        return DB::all("SELECT c.id, c.tenchinhanh, COUNT(d.id) AS sodon, COALESCE(SUM(d.tongtien),0) AS doanhthu
                        FROM chinhanh c LEFT JOIN donhang d ON d.chinhanh_id = c.id AND d.trangthai <> 4
                             AND d.ngay >= DATE_SUB(CURDATE(), INTERVAL $days DAY)
                        GROUP BY c.id, c.tenchinhanh ORDER BY doanhthu DESC");
    }
    public static function doanhThuTheoNgay($cn = null, $days = 14) {
        $days = max(1, (int)$days);
        $w = $cn ? " AND chinhanh_id = " . (int)$cn : "";
        return DB::all("SELECT DATE(ngay) AS ngay, COUNT(*) AS sodon, SUM(tongtien) AS doanhthu FROM donhang
                        WHERE trangthai <> 4 AND ngay >= DATE_SUB(CURDATE(), INTERVAL $days DAY) $w
                        GROUP BY DATE(ngay) ORDER BY ngay DESC");
    }
    public static function topMon($cn = null, $days = 30, $limit = 5) {
        $days = max(1, (int)$days); $limit = max(1, (int)$limit);
        $w = $cn ? " AND d.chinhanh_id = " . (int)$cn : "";
        return DB::all("SELECT ct.tenmon, SUM(ct.soluong) AS sl, SUM(ct.thanhtien) AS doanhthu
                        FROM donhangct ct JOIN donhang d ON d.id = ct.donhang_id
                        WHERE d.trangthai <> 4 AND d.ngay >= DATE_SUB(CURDATE(), INTERVAL $days DAY) $w
                        GROUP BY ct.monan_id, ct.tenmon ORDER BY sl DESC LIMIT $limit");
    }
    public static function sapHetHang($cn = null, $nguong = 5) {
        $w = $cn ? " AND mc.chinhanh_id = " . (int)$cn : "";
        return DB::all("SELECT c.tenchinhanh, m.tenmon, mc.soluongton FROM monan_chinhanh mc
                        JOIN monan m ON m.id = mc.monan_id JOIN chinhanh c ON c.id = mc.chinhanh_id
                        WHERE mc.conphucvu = 1 AND m.trangthai = 1 AND mc.soluongton <= " . (int)$nguong . " $w
                        ORDER BY mc.soluongton, c.id LIMIT 30");
    }
}
