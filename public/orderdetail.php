<?php include 'inc/top.php'; [$lb, $cl] = trangthai_don($d['trangthai']); ?>
<h4 class="section-title mb-3">Đơn <?= maDon($d['id']) ?> <span class="badge text-bg-<?= $cl ?> fs-6"><?= $lb ?></span></h4>
<div class="card border-0 shadow-sm"><div class="card-body">
  <p class="mb-1">Chi nhánh: <strong><?= e($d['tenchinhanh']) ?></strong> · <?= hinhthuc_don($d['hinhthuc']) ?> · <?= date('d/m/Y H:i', strtotime($d['ngay'])) ?></p>
  <p class="mb-1">Người nhận: <?= e($d['hoten_nhan']) ?> - <?= e($d['sodt_nhan']) ?></p>
  <?php if ($d['diachi']): ?><p class="mb-1">Địa chỉ: <?= e($d['diachi']) ?></p><?php endif; ?>
  <?php if ($d['ghichu']): ?><p class="mb-1">Ghi chú: <?= e($d['ghichu']) ?></p><?php endif; ?>
  <table class="table table-sm mt-3">
    <thead><tr><th>Món</th><th class="text-end">Đơn giá</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
    <?php foreach ($ct as $c): ?><tr><td><?= e($c['tenmon']) ?></td><td class="text-end"><?= money($c['dongia']) ?></td><td class="text-end"><?= (int)$c['soluong'] ?></td><td class="text-end"><?= money($c['thanhtien']) ?></td></tr><?php endforeach; ?>
    <?php if ($d['phigiao'] > 0): ?><tr><td colspan="3" class="text-end">Phí giao hàng</td><td class="text-end"><?= money($d['phigiao']) ?></td></tr><?php endif; ?>
    <tr class="fw-bold"><td colspan="3" class="text-end">Tổng cộng</td><td class="text-end"><?= money($d['tongtien']) ?></td></tr>
  </table>
  <div class="d-flex gap-2">
    <a href="index.php?action=donhang" class="btn btn-outline-secondary">← Quay lại</a>
    <form method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="datlai"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="btn btn-outline-gg">↻ Đặt lại đơn này</button></form>
    <?php if ((int)$d['trangthai'] === 0): ?>
      <form method="post" action="index.php" onsubmit="return confirm('Hủy đơn hàng này?')"><?= csrf_field() ?><input type="hidden" name="action" value="huydon"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="btn btn-outline-danger">Hủy đơn</button></form>
    <?php endif; ?>
  </div>
</div></div>
<?php include 'inc/bottom.php'; ?>
