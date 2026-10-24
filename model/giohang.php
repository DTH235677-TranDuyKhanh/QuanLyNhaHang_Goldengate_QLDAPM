<?php
// Giỏ hàng lưu trong SESSION: [monan_id => soluong]
class GioHang {
    const MAX_MOI_MON = 20;

    public static function raw() { return $_SESSION['giohang'] ?? []; }
    public static function dem() { return array_sum(self::raw()); }
    public static function trong() { return count(self::raw()) === 0; }
    public static function xoaTatCa() { unset($_SESSION['giohang']); }

    public static function them($id, $sl) {
        $id = (int)$id; $sl = (int)$sl;
        if ($id <= 0 || $sl <= 0) return;
        $g = self::raw();
        $g[$id] = min(self::MAX_MOI_MON, ($g[$id] ?? 0) + $sl);
        $_SESSION['giohang'] = $g;
    }
    public static function dat($id, $sl) {
        $id = (int)$id; $sl = (int)$sl;
        $g = self::raw();
        if ($sl <= 0) unset($g[$id]); else $g[$id] = min(self::MAX_MOI_MON, $sl);
        $_SESSION['giohang'] = $g;
    }
    public static function xoa($id) { self::dat($id, 0); }
    public static function soLuong($id) { return self::raw()[(int)$id] ?? 0; }

    // Chi tiết dòng giỏ hàng theo chi nhánh hiện tại
    public static function dong($cn) {
        $g = self::raw();
        if (!$g) return [];
        $rows = MonAn::layTheoIds(array_keys($g), $cn);
        $map = [];
        foreach ($rows as $r) $map[(int)$r['id']] = $r;
        $out = [];
        foreach ($g as $id => $sl) {
            if (!isset($map[$id])) { unset($_SESSION['giohang'][$id]); continue; }  // món không còn phục vụ
            $m = $map[$id];
            $m['soluong'] = $sl;
            $m['thanhtien'] = $sl * (float)$m['giaban'];
            $m['thieu'] = $sl > (int)$m['soluongton'];
            $out[] = $m;
        }
        return $out;
    }
    public static function tamTinh($dong) { return array_sum(array_column($dong, 'thanhtien')); }
    public static function phiGiao($hinhthuc, $tamtinh) {
        if ((int)$hinhthuc !== 3) return 0;
        return $tamtinh >= cfg('free_ship_from') ? 0 : (int)cfg('ship_fee');
    }
}
