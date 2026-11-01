<?php include 'inc/top.php'; ?>
<div class="row g-4">
  <div class="col-lg-5">
    <form method="post" action="index.php" class="card card-body border-0 shadow-sm">
      <?= csrf_field() ?><input type="hidden" name="action" value="xldatban">
      <h4 class="section-title mb-1">Gửi yêu cầu đặt bàn</h4>
      <p class="text-muted small">Nhà hàng sẽ liên hệ để xác nhận. Gửi yêu cầu chưa đồng nghĩa bàn đã được giữ.</p>
      <label class="form-label" for="bookingName">Họ tên *</label>
      <input id="bookingName" class="form-control mb-3" name="hoten" maxlength="255" required value="<?= e($bookingForm['hoten']) ?>">
      <label class="form-label" for="bookingPhone">Số điện thoại *</label>
      <input id="bookingPhone" class="form-control mb-3" type="tel" name="sdt" pattern="0[0-9]{9}" required value="<?= e($bookingForm['sdt']) ?>" placeholder="0123456789">
      <label class="form-label" for="bookingBranch">Chi nhánh *</label>
      <select id="bookingBranch" class="form-select mb-3" name="chinhanh_id" required>
        <?php foreach ($chinhanhs as $branch): ?>
          <option value="<?= (int)$branch['id'] ?>" <?= (int)$bookingForm['chinhanh_id'] === (int)$branch['id'] ? 'selected' : '' ?>>
            <?= e($branch['tenchinhanh']) ?> (<?= e(substr($branch['giomocua'], 0, 5)) ?>–<?= e(substr($branch['giodongcua'], 0, 5)) ?>)
          </option>
        <?php endforeach; ?>
      </select>
      <label class="form-label" for="bookingTime">Ngày và giờ *</label>
      <input id="bookingTime" class="form-control mb-3" type="datetime-local" name="thoi_gian" required value="<?= e($bookingForm['thoi_gian']) ?>">
      <label class="form-label" for="bookingGuests">Số khách *</label>
      <input id="bookingGuests" class="form-control mb-3" type="number" min="1" max="30" name="so_khach" required value="<?= (int)$bookingForm['so_khach'] ?>">
      <label class="form-label" for="bookingNote">Ghi chú</label>
      <textarea id="bookingNote" class="form-control mb-3" name="ghichu" maxlength="500" rows="3" placeholder="Nhu cầu đặc biệt (nếu có)"><?= e($bookingForm['ghichu']) ?></textarea>
      <button class="btn btn-gg">Gửi yêu cầu đặt bàn</button>
    </form>
  </div>
  <div class="col-lg-7">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <h4 class="section-title">Yêu cầu của tôi</h4>
        <?php if (!$user): ?>
          <p class="text-muted mb-0">Bạn có thể gửi yêu cầu không cần đăng nhập. Đăng nhập để xem và hủy yêu cầu đang chờ xác nhận.</p>
        <?php elseif (!$bookings): ?>
          <p class="text-muted mb-0">Bạn chưa có yêu cầu đặt bàn.</p>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead><tr><th>Thời gian</th><th>Chi nhánh</th><th>Khách</th><th>Trạng thái</th><th></th></tr></thead>
              <tbody>
              <?php foreach ($bookings as $booking): [$label, $color] = DatBan::nhanTrangThai($booking['trangthai']); ?>
                <tr>
                  <td class="text-nowrap"><?= date('d/m/Y H:i', strtotime($booking['thoi_gian'])) ?></td>
                  <td><?= e($booking['tenchinhanh']) ?></td>
                  <td><?= (int)$booking['so_khach'] ?></td>
                  <td><span class="badge text-bg-<?= e($color) ?>"><?= e($label) ?></span></td>
                  <td>
                    <?php if ((int)$booking['trangthai'] === DatBan::CHO_XAC_NHAN): ?>
                      <form method="post" action="index.php" onsubmit="return confirm('Hủy yêu cầu đặt bàn này?')">
                        <?= csrf_field() ?><input type="hidden" name="action" value="huydatban"><input type="hidden" name="id" value="<?= (int)$booking['id'] ?>">
                        <button class="btn btn-sm btn-outline-danger">Hủy</button>
                      </form>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
