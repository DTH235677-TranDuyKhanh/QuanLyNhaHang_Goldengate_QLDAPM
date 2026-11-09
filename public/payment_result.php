<?php include 'inc/top.php'; [$paymentLabel, $paymentColor] = trangthai_thanh_toan($d['trangthai_tt']); ?>
<div class="row justify-content-center">
  <div class="col-12 col-lg-8">
    <div class="card border-0 shadow-sm"><div class="card-body p-4 p-md-5 text-center">
      <?php if ($d['trangthai_tt'] === 'paid'): ?>
        <div class="display-4 text-success mb-3">✓</div>
        <h2>Thanh toán thành công</h2>
        <p>VNPay đã xác nhận thanh toán đơn <strong><?= e(maDon($d['id'])) ?></strong>.</p>
      <?php elseif ($d['trangthai_tt'] === 'pending'): ?>
        <div class="display-4 text-primary mb-3">…</div>
        <h2>Đang xác nhận giao dịch</h2>
        <p>Đơn <?= e(maDon($d['id'])) ?> đang chờ thông báo xác thực từ máy chủ VNPay. Vui lòng không thanh toán lại.</p>
      <?php elseif ($d['trangthai_tt'] === 'manual_review'): ?>
        <div class="display-4 text-warning mb-3">!</div>
        <h2>Giao dịch cần đối soát</h2>
        <p>Nhà hàng sẽ kiểm tra giao dịch đơn <?= e(maDon($d['id'])) ?>. Vui lòng liên hệ chi nhánh và không thanh toán lại.</p>
      <?php else: ?>
        <div class="display-4 text-danger mb-3">×</div>
        <h2>Thanh toán chưa hoàn tất</h2>
        <p>Trạng thái hiện tại: <span class="badge text-bg-<?= e($paymentColor) ?>"><?= e($paymentLabel) ?></span>. Đơn hàng đã được giải phóng tồn kho nếu giao dịch thất bại hoặc hết hạn.</p>
      <?php endif; ?>
      <p class="text-muted">Đơn hàng: <?= e(maDon($d['id'])) ?> · Tổng tiền: <?= money($d['tongtien']) ?></p>
      <a class="btn btn-gg" href="index.php?action=chitietdon&id=<?= (int)$d['id'] ?>">Xem chi tiết đơn</a>
      <a class="btn btn-outline-secondary ms-2" href="index.php">Về trang chủ</a>
    </div></div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
