<?php include 'inc/top.php'; [$lb, $cl] = trangthai_don($d['trangthai']); ?>
<h4 class="section-title mb-3">Đơn <?= maDon($d['id']) ?> <span class="badge text-bg-<?= $cl ?> fs-6"><?= $lb ?></span></h4>
<div class="card border-0 shadow-sm"><div class="card-body">
  <p class="mb-1">Chi nhánh: <strong><?= e($d['tenchinhanh']) ?></strong> · <?= hinhthuc_don($d['hinhthuc']) ?> · <?= date('d/m/Y H:i', strtotime($d['ngay'])) ?></p>
  <?php [$paymentLabel, $paymentColor] = trangthai_thanh_toan($d['trangthai_tt']); ?>
  <p class="mb-1">Thanh toán: <?= e($d['phuongthuc_tt'] === 'vnpay' ? 'VNPay' : 'Khi nhận món') ?> · <span class="badge text-bg-<?= e($paymentColor) ?>"><?= e($paymentLabel) ?></span></p>
  <?php if ($d['trangthai_tt'] === 'manual_review'): ?><div class="alert alert-warning mt-2">Nhà hàng đang đối soát giao dịch. Vui lòng liên hệ chi nhánh, không thanh toán lại đơn này.</div><?php endif; ?>
  <p class="mb-1">Người nhận: <?= e($d['hoten_nhan']) ?> - <?= e($d['sodt_nhan']) ?></p>
  <?php if ($d['diachi']): ?><p class="mb-1">Địa chỉ: <?= e($d['diachi']) ?></p><?php endif; ?>
  <?php if ($d['ghichu']): ?><p class="mb-1">Ghi chú: <?= e($d['ghichu']) ?></p><?php endif; ?>
  <?php if (!empty($d['voucher_ma'])): ?><p class="mb-1">Voucher: <strong><?= e($d['voucher_ma']) ?></strong></p><?php endif; ?>
  <table class="table table-sm mt-3">
    <thead><tr><th>Món</th><th class="text-end">Đơn giá</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
    <?php foreach ($ct as $c): ?><tr><td><?= e($c['tenmon']) ?></td><td class="text-end"><?= money($c['dongia']) ?></td><td class="text-end"><?= (int)$c['soluong'] ?></td><td class="text-end"><?= money($c['thanhtien']) ?></td></tr><?php endforeach; ?>
    <?php if ($d['phigiao'] > 0): ?><tr><td colspan="3" class="text-end">Phí giao hàng</td><td class="text-end"><?= money($d['phigiao']) ?></td></tr><?php endif; ?>
    <?php if ((int)$d['giamgia'] > 0): ?><tr><td colspan="3" class="text-end">Voucher giảm</td><td class="text-end text-success">-<?= money($d['giamgia']) ?></td></tr><?php endif; ?>
    <tr class="fw-bold"><td colspan="3" class="text-end">Tổng cộng</td><td class="text-end"><?= money($d['tongtien']) ?></td></tr>
  </table>
  <div class="d-flex gap-2">
    <a href="index.php?action=donhang" class="btn btn-outline-secondary">← Quay lại</a>
    <form method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="datlai"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="btn btn-outline-gg">↻ Đặt lại đơn này</button></form>
    <?php if ((int)$d['trangthai'] === 0 && $d['trangthai_tt'] !== 'paid'): ?>
      <form method="post" action="index.php" onsubmit="return confirm('Hủy đơn hàng này?')"><?= csrf_field() ?><input type="hidden" name="action" value="huydon"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>"><button class="btn btn-outline-danger">Hủy đơn</button></form>
    <?php endif; ?>
  </div>
</div></div>
<?php if ($history): ?>
  <section class="card border-0 shadow-sm mt-3"><div class="card-body">
    <h5 class="mb-3">Lịch sử đơn hàng</h5>
    <div class="table-responsive"><table class="table table-sm mb-0">
      <thead><tr><th>Thời gian</th><th>Trạng thái</th><th>Ghi chú</th></tr></thead>
      <tbody><?php foreach ($history as $entry): ?>
        <tr><td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($entry['ngaytao'])) ?></td><td><?= e(trangthai_don($entry['trangthai_moi'])[0]) ?></td><td><?= e($entry['ghichu'] ?? '') ?></td></tr>
      <?php endforeach; ?></tbody>
    </table></div>
  </div></section>
<?php endif; ?>
<?php if ((int)$d['trangthai'] === 3 && $user): ?>
  <section class="card border-0 shadow-sm mt-4"><div class="card-body">
    <h5 class="section-title mb-3">Đánh giá món trong đơn</h5>
    <?php foreach ($ct as $item): $review = $reviewsByFood[(int)$item['monan_id']] ?? null; ?>
      <div class="border-bottom py-3 last-border-0">
        <strong><?= e($item['tenmon']) ?></strong>
        <?php if ($review): ?>
          <div class="text-warning mt-1"><?= str_repeat('★', (int)$review['so_sao']) . str_repeat('☆', 5 - (int)$review['so_sao']) ?></div>
          <p class="mb-0"><?= nl2br(e($review['noi_dung'])) ?></p>
        <?php else: ?>
          <form method="post" action="index.php" class="mt-2">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="danhgia">
            <input type="hidden" name="donhang_id" value="<?= (int)$d['id'] ?>">
            <input type="hidden" name="monan_id" value="<?= (int)$item['monan_id'] ?>">
            <div class="row g-2">
              <div class="col-md-3"><label class="form-label" for="rating<?= (int)$item['monan_id'] ?>">Số sao</label>
                <select class="form-select" id="rating<?= (int)$item['monan_id'] ?>" name="so_sao" required>
                  <option value="5">5 - Rất hài lòng</option><option value="4">4 - Hài lòng</option><option value="3">3 - Bình thường</option><option value="2">2 - Chưa hài lòng</option><option value="1">1 - Không hài lòng</option>
                </select>
              </div>
              <div class="col-md-9"><label class="form-label" for="review<?= (int)$item['monan_id'] ?>">Nhận xét</label>
                <textarea class="form-control" id="review<?= (int)$item['monan_id'] ?>" name="noi_dung" maxlength="1000" rows="2" required></textarea>
              </div>
              <div class="col-12"><button class="btn btn-sm btn-gg">Gửi đánh giá</button></div>
            </div>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div></section>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
