<?php
class DanhGia {
    public static function theoMon($foodId) {
        return DB::all("SELECT dg.so_sao, dg.noi_dung, dg.phan_hoi, dg.phan_hoi_luc, dg.ngaytao, nd.hoten
                        FROM danhgia dg
                        JOIN nguoidung nd ON nd.id = dg.nguoidung_id
                        WHERE dg.monan_id = ? AND dg.trangthai = 1
                        ORDER BY dg.ngaytao DESC, dg.id DESC
                        LIMIT 50", [(int)$foodId]);
    }

    public static function tongQuanMon($foodId) {
        return DB::one("SELECT COUNT(*) AS so_luot, COALESCE(AVG(so_sao), 0) AS diem_tb
                        FROM danhgia WHERE monan_id = ? AND trangthai = 1", [(int)$foodId]);
    }

    public static function cuaDon($orderId, $userId) {
        return DB::all("SELECT dg.monan_id, dg.so_sao, dg.noi_dung, dg.ngaytao,
                               dg.phan_hoi, dg.phan_hoi_luc
                        FROM danhgia dg
                        JOIN donhang dh ON dh.id = dg.donhang_id
                        WHERE dg.donhang_id = ? AND dh.nguoidung_id = ?", [(int)$orderId, (int)$userId]);
    }

    public static function tatCaAdmin() {
        return DB::all("SELECT dg.*, nd.hoten, m.tenmon, dh.id AS donhang_ma
                        FROM danhgia dg
                        JOIN nguoidung nd ON nd.id = dg.nguoidung_id
                        JOIN monan m ON m.id = dg.monan_id
                        JOIN donhang dh ON dh.id = dg.donhang_id
                        ORDER BY dg.ngaytao DESC, dg.id DESC
                        LIMIT 300");
    }

    public static function tao($userId, $orderId, $foodId, $rating, $content) {
        $rating = (int)$rating;
        $content = trim((string)$content);
        if ($rating < 1 || $rating > 5) {
            throw new RuntimeException('Số sao đánh giá phải từ 1 đến 5.');
        }
        if ($content === '' || !preg_match('//u', $content)) {
            throw new RuntimeException('Vui lòng nhập nội dung phản hồi hợp lệ.');
        }
        $length = preg_match_all('/./us', $content, $matches);
        if ($length === false || $length > 1000) {
            throw new RuntimeException('Nội dung phản hồi không được vượt quá 1000 ký tự.');
        }

        $purchased = DB::one("SELECT dh.id
                              FROM donhang dh
                              JOIN donhangct ct ON ct.donhang_id = dh.id
                              WHERE dh.id = ? AND dh.nguoidung_id = ?
                                AND dh.trangthai = 3 AND ct.monan_id = ?",
            [(int)$orderId, (int)$userId, (int)$foodId]);
        if (!$purchased) {
            throw new RuntimeException('Chỉ có thể đánh giá món thuộc đơn hàng đã hoàn thành của bạn.');
        }

        try {
            return DB::insert("INSERT INTO danhgia
                (nguoidung_id, donhang_id, monan_id, so_sao, noi_dung)
                VALUES (?,?,?,?,?)",
                [(int)$userId, (int)$orderId, (int)$foodId, $rating, $content]);
        } catch (PDOException $e) {
            if (isset($e->errorInfo[1]) && (int)$e->errorInfo[1] === 1062) {
                throw new RuntimeException('Bạn đã gửi đánh giá cho món này trong đơn hàng.');
            }
            throw $e;
        }
    }

    public static function doiTrangThai($id) {
        return DB::exec("UPDATE danhgia SET trangthai = 1 - trangthai WHERE id = ?", [(int)$id]);
    }

    public static function phanHoi($id, $content, $userId) {
        $content = trim((string)$content);
        $length = preg_match_all('/./us', $content, $matches);
        if ($length === false || $length > 1000) {
            throw new RuntimeException('Phản hồi không được vượt quá 1000 ký tự và phải dùng văn bản hợp lệ.');
        }
        if (!DB::val("SELECT id FROM danhgia WHERE id = ?", [(int)$id])) {
            throw new RuntimeException('Không tìm thấy đánh giá cần phản hồi.');
        }
        return DB::exec("UPDATE danhgia SET phan_hoi = ?, phan_hoi_luc = ?, phan_hoi_boi = ?
                         WHERE id = ?",
            [$content !== '' ? $content : null, $content !== '' ? DB::val('SELECT NOW()') : null,
             $content !== '' ? (int)$userId : null, (int)$id]);
    }
}
