<?php include 'inc/top.php'; ?>
<h4 class="section-title mb-3">Giỏ hàng - <?= e($cb['tenchinhanh'] ?? '') ?></h4>
<?php if (!$dong): ?>
  <div class="alert alert-info">Giỏ hàng đang trống. <a href="index.php">Xem thực đơn</a></div>
<?php else: ?>
<form method="post" action="index.php">
  <?= csrf_field() ?><input type="hidden" name="action" value="capnhatgio">
  <div class="table-responsive">
  <table class="table align-middle bg-white shadow-sm rounded">
    <thead><tr><th>Món</th><th class="text-end">Đơn giá</th><th style="width:120px">Số lượng</th><th class="text-end">Thành tiền</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($dong as $d): ?>
      <tr class="<?= $d['thieu'] ? 'table-warning' : '' ?>">
        <td><a class="text-dark fw-semibold text-decoration-none" href="index.php?action=detail&id=<?= (int)$d['id'] ?>"><?= e($d['tenmon']) ?></a>
          <?php if ($d['thieu']): ?><div class="small text-danger">Chỉ còn <?= (int)$d['soluongton'] ?> phần - vui lòng giảm số lượng</div><?php endif; ?></td>
        <td class="text-end"><?= money($d['giaban']) ?></td>
        <td><input type="number" class="form-control form-control-sm" name="soluong[<?= (int)$d['id'] ?>]" value="<?= (int)$d['soluong'] ?>" min="0" max="20"></td>
        <td class="text-end"><?= money($d['thanhtien']) ?></td>
        <td class="text-end">
          <button type="submit" form="xoa<?= (int)$d['id'] ?>" class="btn btn-sm btn-outline-danger">Xóa</button>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot><tr><th colspan="3" class="text-end">Tạm tính</th><th class="text-end fs-5 price"><?= money($tam) ?></th><th></th></tr></tfoot>
  </table>
  </div>
  <div class="d-flex gap-2 flex-wrap">
    <a href="index.php" class="btn btn-outline-secondary">← Chọn thêm món</a>
    <button class="btn btn-outline-gg">Cập nhật giỏ hàng</button>
    <a href="index.php?action=thanhtoan" class="btn btn-gg ms-auto">Tiếp tục đặt món →</a>
  </div>
</form>
<?php foreach ($dong as $d): ?>
  <form id="xoa<?= (int)$d['id'] ?>" method="post" action="index.php">
    <?= csrf_field() ?><input type="hidden" name="action" value="xoamon"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
  </form>
<?php endforeach; ?>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
