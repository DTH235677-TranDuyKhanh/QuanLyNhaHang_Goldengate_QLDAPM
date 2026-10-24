<?php
/**
 * CÁ NHÂN HÓA: "Dành riêng cho bạn"
 *  - Gọi lại món quen : món khách đã gọi nhiều nhất (từ lịch sử đơn hàng)
 *  - Có thể bạn sẽ thích: món CHƯA gọi, chấm điểm theo
 *        3 x độ quan tâm (số lần xem, tối đa 5, chuẩn hóa 0..1)
 *      + 2 x độ thích nhóm món (nhóm hay gọi/hay xem, chuẩn hóa 0..1)
 *      + 0.5 x độ phổ biến (luotgoi chuẩn hóa)
 *  - Khách chưa đăng nhập: dùng cookie món đã xem. Chưa có dữ liệu: hiển thị món bán chạy.
 */
class GoiY {
    public static function ghiNhanXem($uid, $monId) {
        DB::exec("INSERT INTO luotxem_nguoidung (nguoidung_id, monan_id, soluong) VALUES (?,?,1)
                  ON DUPLICATE KEY UPDATE soluong = soluong + 1, lancuoi = NOW()", [(int)$uid, (int)$monId]);
    }

    public static function cho($uid, $cn, array $daXemCookie, $n = 4) {
        $bought = []; $viewed = [];
        if ($uid) {
            foreach (DB::all("SELECT ct.monan_id, SUM(ct.soluong) AS sl FROM donhangct ct
                              JOIN donhang d ON d.id = ct.donhang_id
                              WHERE d.nguoidung_id = ? AND d.trangthai <> 4 GROUP BY ct.monan_id", [(int)$uid]) as $r) {
                $bought[(int)$r['monan_id']] = (int)$r['sl'];
            }
            foreach (DB::all("SELECT monan_id, soluong FROM luotxem_nguoidung WHERE nguoidung_id = ?", [(int)$uid]) as $r) {
                $viewed[(int)$r['monan_id']] = (int)$r['soluong'];
            }
        } else {
            foreach ($daXemCookie as $id) $viewed[(int)$id] = 1;
        }

        $mons = array_values(array_filter(MonAn::layTheoChiNhanh($cn), fn($m) => (int)$m['soluongton'] > 0));
        $coDuLieu = ($bought || $viewed);
        if (!$coDuLieu) {
            return ['canhan' => false, 'quen' => [], 'khampha' => self::gan(MonAn::banChay($cn, $n), 'Đang được gọi nhiều')];
        }

        // Điểm theo nhóm món: lấy nhóm của mọi món từng gọi/xem (kể cả món chi nhánh này không phục vụ)
        $ids = array_unique(array_merge(array_keys($bought), array_keys($viewed)));
        $nhomCua = [];
        $in = implode(',', array_fill(0, count($ids), '?'));
        foreach (DB::all("SELECT id, nhom_id FROM monan WHERE id IN ($in)", array_values($ids)) as $r) $nhomCua[(int)$r['id']] = (int)$r['nhom_id'];
        $nhomDiem = [];
        foreach ($bought as $id => $sl) { $g = $nhomCua[$id] ?? 0; $nhomDiem[$g] = ($nhomDiem[$g] ?? 0) + 2 * $sl; }
        foreach ($viewed as $id => $sl) { $g = $nhomCua[$id] ?? 0; $nhomDiem[$g] = ($nhomDiem[$g] ?? 0) + min($sl, 5); }
        $maxNhom = $nhomDiem ? max($nhomDiem) : 1;
        $maxGoi = 1;
        foreach ($mons as $m) $maxGoi = max($maxGoi, (int)$m['luotgoi']);

        // 1) Món quen
        $quen = [];
        foreach ($mons as $m) {
            $id = (int)$m['id'];
            if (!empty($bought[$id])) { $m['ly_do'] = 'Bạn đã gọi ' . $bought[$id] . ' lần'; $m['_s'] = $bought[$id]; $quen[] = $m; }
        }
        usort($quen, fn($a, $b) => $b['_s'] <=> $a['_s']);
        $quen = array_slice($quen, 0, $n);

        // 2) Khám phá món mới
        $kp = [];
        foreach ($mons as $m) {
            $id = (int)$m['id'];
            if (!empty($bought[$id])) continue;
            $v = min($viewed[$id] ?? 0, 5) / 5;
            $g = ($nhomDiem[(int)$m['nhom_id']] ?? 0) / $maxNhom;
            $p = (int)$m['luotgoi'] / $maxGoi;
            $m['_s'] = 3 * $v + 2 * $g + 0.5 * $p;
            $m['ly_do'] = ($viewed[$id] ?? 0) > 0 ? 'Bạn đã xem món này'
                        : ($g > 0 ? 'Vì bạn hay chọn nhóm "' . $m['tennhom'] . '"' : 'Đang được gọi nhiều');
            $kp[] = $m;
        }
        usort($kp, fn($a, $b) => $b['_s'] <=> $a['_s']);
        return ['canhan' => true, 'quen' => $quen, 'khampha' => array_slice($kp, 0, $n)];
    }

    private static function gan(array $rows, $lyDo) { foreach ($rows as &$r) $r['ly_do'] = $lyDo; return $rows; }

    // Đặt lại một đơn cũ vào giỏ (bỏ qua món hết/không phục vụ ở chi nhánh hiện tại)
    public static function datLai($uid, $donId, $cn) {
        $d = DonHang::laySo($donId);
        if (!$d || (int)$d['nguoidung_id'] !== (int)$uid) throw new RuntimeException('Không tìm thấy đơn hàng.');
        $them = 0; $boQua = 0;
        foreach (DonHang::chiTiet($donId) as $ct) {
            $m = MonAn::layTheoId($ct['monan_id'], $cn);
            if (!$m || !$m['trangthai'] || !$m['conphucvu'] || (int)$m['soluongton'] <= 0) { $boQua++; continue; }
            GioHang::them($ct['monan_id'], min((int)$ct['soluong'], (int)$m['soluongton']));
            $them++;
        }
        return [$them, $boQua];
    }
    public static function donGanNhat($uid) {
        $d = DB::one("SELECT * FROM donhang WHERE nguoidung_id = ? AND trangthai <> 4 ORDER BY id DESC LIMIT 1", [(int)$uid]);
        if ($d) $d['items'] = DonHang::chiTiet($d['id']);
        return $d;
    }
}
