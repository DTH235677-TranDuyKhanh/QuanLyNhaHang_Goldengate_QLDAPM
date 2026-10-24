<?php include 'inc/top.php'; ?>
<h4 class="section-title mb-3">Đơn hàng của tôi</h4>
<?php if (!$dons): ?><div class="alert alert-info">Bạn chưa có đơn hàng nào.</div><?php else: ?>
<div class="table-responsive"><table class="table bg-white align-middle shadow-sm">
  <thead><tr><th>Mã đơn</th><th>Ngày</th><th>Chi nhánh</th><th>Hình thức</th><th class="text-end">Tổng</th><th>Trạng thái</th><th></th></tr></thead>
  <tbody>
  <?php foreach ($dons as $d): [$lb, $cl] = trangthai_don($d['trangthai']); ?>
    <tr>
      <td><?= maDon($d['id']) ?></td><td><?= date('d/m/Y H:i', strtotime($d['ngay'])) ?></td><td><?= e($d['tenchinhanh']) ?></td>
      <td><?= hinhthuc_don($d['hinhthuc']) ?></td><td class="text-end"><?= money($d['tongtien']) ?></td>
      <td><span class="badge text-bg-<?= $cl ?>"><?= $lb ?></span></td>
      <td class="text-end"><a class="btn btn-sm btn-outline-secondary" href="index.php?action=chitietdon&id=<?= (int)$d['id'] ?>">Chi tiết</a></td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table></div>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
