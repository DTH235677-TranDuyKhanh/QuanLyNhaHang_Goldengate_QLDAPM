<?php include 'inc/top.php'; ?>
<h3 class="mb-3">Đơn hàng</h3>
<form method="get" class="row g-2 mb-3"><input type="hidden" name="action" value="donhang">
  <?php if ($isAdmin): ?><div class="col-auto"><select name="cn" class="form-select"><option value="">Tất cả chi nhánh</option>
    <?php foreach ($cns as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$cn ? 'selected' : '' ?>><?= e($c['tenchinhanh']) ?></option><?php endforeach; ?></select></div><?php endif; ?>
  <div class="col-auto"><select name="tt" class="form-select"><option value="">Mọi trạng thái</option>
    <?php foreach ([0, 1, 2, 3, 4] as $s): ?><option value="<?= $s ?>" <?= (string)$tt === (string)$s ? 'selected' : '' ?>><?= trangthai_don($s)[0] ?></option><?php endforeach; ?></select></div>
  <div class="col-auto"><button class="btn btn-gg">Lọc</button></div>
</form>
<table class="table table-sm bg-white shadow-sm align-middle">
  <thead><tr><th>Mã</th><th>Thời gian</th><th>Chi nhánh</th><th>Khách</th><th>Hình thức</th><th class="text-end">Tổng</th><th>Trạng thái</th><th></th></tr></thead>
  <?php foreach ($ds as $d): [$lb, $cl] = trangthai_don($d['trangthai']); ?><tr>
    <td><?= maDon($d['id']) ?></td><td><?= date('d/m H:i', strtotime($d['ngay'])) ?></td><td><?= e($d['tenchinhanh']) ?></td>
    <td><?= e($d['hoten_nhan']) ?><br><small class="text-muted"><?= e($d['sodt_nhan']) ?></small></td><td><?= hinhthuc_don($d['hinhthuc']) ?></td>
    <td class="text-end"><?= money($d['tongtien']) ?></td><td><span class="badge text-bg-<?= $cl ?>"><?= $lb ?></span></td>
    <td><a class="btn btn-sm btn-outline-primary" href="index.php?action=chitietdon&id=<?= (int)$d['id'] ?>">Xem</a></td>
  </tr><?php endforeach; ?>
  <?php if (!$ds): ?><tr><td colspan="8" class="text-center text-muted">Không có đơn hàng.</td></tr><?php endif; ?>
</table>
<?php include 'inc/bottom.php'; ?>
