<?php include 'inc/top.php'; [$lb, $cl] = trangthai_don($d['trangthai']); ?>
<div class="admin-page-heading">
  <div>
    <h3>Đơn <?= e(maDon($d['id'])) ?> <span class="badge text-bg-<?= e($cl) ?>"><?= e($lb) ?></span></h3>
    <p>Đặt lúc <?= date('d/m/Y H:i', strtotime($d['ngay'])) ?> · <?= e($d['tenchinhanh']) ?></p>
  </div>
  <a href="index.php?action=donhang" class="btn btn-outline-secondary">← Danh sách đơn</a>
</div>
<div class="row g-3">
  <div class="col-12 col-xl-8">
    <div class="card">
      <div class="card-body">
        <h5 class="mb-3">Thông tin nhận hàng</h5>
        <div class="row g-3 mb-3">
          <div class="col-sm-6"><div class="small text-muted">Khách hàng</div><div class="fw-semibold"><?= e($d['hoten_nhan']) ?></div></div>
          <div class="col-sm-6"><div class="small text-muted">Số điện thoại</div><div class="fw-semibold"><?= e($d['sodt_nhan']) ?></div></div>
          <div class="col-sm-6"><div class="small text-muted">Hình thức</div><div><?= e(hinhthuc_don($d['hinhthuc'])) ?></div></div>
          <?php [$paymentLabel, $paymentColor] = trangthai_thanh_toan($d['trangthai_tt']); ?>
          <div class="col-sm-6"><div class="small text-muted">Thanh toán</div><div><?= e($d['phuongthuc_tt'] === 'vnpay' ? 'VNPay' : 'Tiền mặt khi nhận') ?> · <span class="badge text-bg-<?= e($paymentColor) ?>"><?= e($paymentLabel) ?></span></div></div>
          <?php if ($d['vnpay_txn_ref']): ?><div class="col-12"><div class="small text-muted">Mã giao dịch</div><div><?= e($d['vnpay_txn_ref']) ?><?= $d['vnpay_transaction_no'] ? ' · ' . e($d['vnpay_transaction_no']) : '' ?></div></div><?php endif; ?>
          <?php if ($d['diachi']): ?><div class="col-12"><div class="small text-muted">Địa chỉ giao hàng</div><div><?= e($d['diachi']) ?></div></div><?php endif; ?>
          <?php if ($d['ghichu']): ?><div class="col-12"><div class="small text-muted">Ghi chú của khách</div><div><?= e($d['ghichu']) ?></div></div><?php endif; ?>
        </div>
        <h5 class="mb-2">Món trong đơn</h5>
        <div class="table-responsive">
          <table class="table">
            <thead><tr><th>Món</th><th class="text-end">Đơn giá</th><th class="text-end">SL</th><th class="text-end">Thành tiền</th></tr></thead>
            <tbody>
              <?php foreach ($ct as $c): ?>
                <tr><td class="fw-semibold"><?= e($c['tenmon']) ?></td><td class="text-end text-nowrap"><?= money($c['dongia']) ?></td><td class="text-end"><?= (int)$c['soluong'] ?></td><td class="text-end text-nowrap"><?= money($c['thanhtien']) ?></td></tr>
              <?php endforeach; ?>
              <?php if ($d['phigiao'] > 0): ?><tr><td colspan="3" class="text-end">Phí giao hàng</td><td class="text-end"><?= money($d['phigiao']) ?></td></tr><?php endif; ?>
              <?php if ((int)$d['giamgia'] > 0): ?><tr><td colspan="3" class="text-end">Voucher <?= !empty($d['voucher_ma']) ? '(' . e($d['voucher_ma']) . ')' : '' ?></td><td class="text-end text-success">-<?= money($d['giamgia']) ?></td></tr><?php endif; ?>
              <tr class="fw-bold"><td colspan="3" class="text-end">Tổng thanh toán</td><td class="text-end"><?= money($d['tongtien']) ?></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="card mt-3">
      <div class="card-body">
        <h5 class="mb-3">Lịch sử xử lý</h5>
        <?php if (!$history): ?>
          <p class="text-muted mb-0">Chưa có lịch sử trạng thái.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm mb-0">
              <thead><tr><th>Thời gian</th><th>Trạng thái</th><th>Người cập nhật</th><th>Ghi chú</th></tr></thead>
              <tbody>
              <?php foreach ($history as $entry): ?>
                <tr>
                  <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($entry['ngaytao'])) ?></td>
                  <td><?= e(trangthai_don((int)$entry['trangthai_moi'])[0]) ?></td>
                  <td><?= e($entry['nguoi_thay_doi'] ?: 'Hệ thống') ?></td>
                  <td><?= e($entry['ghichu'] ?? '') ?></td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-12 col-xl-4">
    <div class="card admin-form-card">
      <div class="admin-form-card-header">
        <h4>Tiến độ xử lý</h4>
        <p>Chuyển đơn từng bước để bếp và nhân viên theo dõi chính xác.</p>
      </div>
      <div class="admin-form-card-body">
        <?php if ($d['phuongthuc_tt'] === 'cod' && $d['trangthai_tt'] !== 'paid' && (int)$d['trangthai'] !== 4): ?>
          <form method="post" action="index.php" class="mb-3">
            <?= csrf_field() ?><input type="hidden" name="action" value="ghinhantienmat"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
            <button class="btn btn-outline-success w-100" onclick="return confirm('Xác nhận đã nhận đủ tiền mặt?')">Đã nhận tiền mặt</button>
          </form>
        <?php endif; ?>
        <?php if ($d['trangthai_tt'] === 'manual_review'): ?>
          <div class="alert alert-warning">Giao dịch VNPay cần đối soát thủ công trước khi phục vụ hoặc hoàn tiền.</div>
        <?php endif; ?>
        <?php if (in_array((int)$d['trangthai'], [3, 4], true)): ?>
          <div class="alert alert-light mb-0">Đơn hàng đã kết thúc, không thể cập nhật thêm.</div>
        <?php else: ?>
          <?php $nextStatus = (int)$d['trangthai'] + 1; ?>
          <form method="post" action="index.php" class="d-grid gap-2">
            <?= csrf_field() ?><input type="hidden" name="action" value="capnhatdon"><input type="hidden" name="id" value="<?= (int)$d['id'] ?>">
            <?php if ($d['phuongthuc_tt'] !== 'vnpay' || $d['trangthai_tt'] === 'paid'): ?>
              <?php if ($nextStatus !== 3 || $d['trangthai_tt'] === 'paid'): ?>
                <button name="trangthai" value="<?= $nextStatus ?>" class="btn btn-primary">Chuyển sang: <?= e(trangthai_don($nextStatus)[0]) ?></button>
              <?php else: ?>
                <div class="alert alert-warning mb-0">Ghi nhận thanh toán trước khi hoàn thành đơn.</div>
              <?php endif; ?>
              <div class="form-text">Chỉ chuyển khi đơn đã hoàn thành bước hiện tại.</div>
            <?php else: ?>
              <div class="alert alert-info mb-0">Chỉ bắt đầu xử lý sau khi VNPay xác nhận thanh toán.</div>
            <?php endif; ?>
            <hr>
            <button name="trangthai" value="4" class="btn btn-outline-danger" onclick="return confirm('Hủy đơn này và hoàn lại tồn kho?')">Hủy đơn & hoàn tồn kho</button>
          </form>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
