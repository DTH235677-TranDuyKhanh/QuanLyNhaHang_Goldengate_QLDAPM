<?php include 'inc/top.php'; [$lb, $cl] = trangthai_don($d['trangthai']); ?>
<h3 class="mb-3">Đơn <?= maDon($d['id']) ?> <span class="badge text-bg-<?= $cl ?>"><?= $lb ?></span></h3>
<div class="row g-3"><div class="col-lg-8"><div class="card card-body border-0 shadow-sm">
  <p class="mb-1"><strong><?= e($d['hoten_nhan']) ?></strong> · <?= e($d['sodt_nhan']) ?></p>
  <p class="mb-1">Chi nhánh <?= e($d['tenchinhanh']) ?> · <?= hinhthuc_don($d['hinhthuc']) ?> · <?= date('d/m/Y H:i', strtotime($d['ngay'])) ?></p>
  <?php if ($d['diachi']): ?><p class="mb-1">Giao đến: <?= e($d['diachi']) ?></p><?php endif; ?>
  <?php if ($d['ghichu']): ?><p class="mb-1">Ghi chú: <em><?= e($d['ghichu']) ?></em></p><?php endif; ?>
  <table class="table table-sm mt-2">
    <?php foreach ($ct as $c): ?><tr><td><?= e($c['tenmon']) ?></td><td class="text-end"><?= money($c['dongia']) ?> × <?= (int)$c['soluong'] ?></td><td class="text-end"><?= money($c['thanhtien']) ?></td></tr><?php endforeach; ?>
    <?php if ($d['phigiao'] > 0): ?><tr><td colspan="2" class="text-end">Phí giao hàng</td><td class="text-end"><?= money($d['phigiao']) ?></td></tr><?php endif; ?>
    <tr class="fw-bold"><td colspan="2" class="text-end">Tổng cộng</td><td class="text-end"><?= money($d['tongtien']) ?></td></tr>
  </table>
</div></div>
<div class="col-lg-4"><div class="card card-body border-0 shadow-sm">
  <h6>Cập nhật trạng thái</h6>
  <?php if (in_array((int)$d['trangthai'], [3, 4], true)): ?><p class="text-muted mb-0">Đơn đã kết thúc.</p>
  <?php else: ?>
    <form method="post" action="index.php" class="d-grid gap-2"><?= csrf_field() ?><input type="hidden" name="action" value="capnhatdon"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
      <?php foreach ([1, 2, 3] as $s): if ($s <= (int)$d['trangthai']) continue; ?>
        <button name="trangthai" value="<?= $s ?>" class="btn btn-outline-primary"><?= trangthai_don($s)[0] ?></button>
      <?php endforeach; ?>
      <button name="trangthai" value="4" class="btn btn-outline-danger" onclick="return confirm('Hủy đơn và hoàn lại tồn kho?')">Hủy đơn</button>
    </form>
  <?php endif; ?>
  <a href="index.php?action=donhang" class="btn btn-link mt-2">← Danh sách đơn</a>
</div></div></div>
<?php include 'inc/bottom.php'; ?>
