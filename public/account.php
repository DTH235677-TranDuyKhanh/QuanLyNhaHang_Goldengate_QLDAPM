<?php include 'inc/top.php'; ?>
<div class="admin-page-heading">
  <div>
    <h3>Tài khoản của tôi</h3>
    <p>Cập nhật thông tin liên hệ và quản lý địa chỉ giao hàng đã lưu.</p>
  </div>
</div>
<div class="row g-3">
  <div class="col-12 col-lg-5">
    <form method="post" action="index.php" class="card card-body border-0 shadow-sm">
      <?= csrf_field() ?><input type="hidden" name="action" value="luuhoso">
      <h5 class="mb-3">Thông tin cá nhân</h5>
      <div class="mb-3">
        <label class="form-label" for="profileName">Họ tên</label>
        <input id="profileName" class="form-control" name="hoten" maxlength="255" required value="<?= e($user['hoten']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="profilePhone">Số điện thoại</label>
        <input id="profilePhone" type="tel" class="form-control" name="sdt" pattern="0[0-9]{9}" required value="<?= e($user['sodienthoai']) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="profileEmail">Email</label>
        <input id="profileEmail" type="email" class="form-control" name="email" maxlength="100" value="<?= e($user['email'] ?? '') ?>">
      </div>
      <button class="btn btn-gg align-self-start">Lưu thông tin</button>
    </form>
  </div>
  <div class="col-12 col-lg-7">
    <div class="card card-body border-0 shadow-sm">
      <h5 class="mb-3">Địa chỉ giao hàng</h5>
      <?php if (!$addresses): ?><p class="text-muted">Bạn chưa lưu địa chỉ nào.</p>
      <?php else: ?>
        <div class="list-group list-group-flush mb-3">
          <?php foreach ($addresses as $address): ?>
            <div class="list-group-item px-0 d-flex flex-wrap justify-content-between align-items-center gap-2">
              <div>
                <span><?= e($address['diachi']) ?></span>
                <?php if ((int)$address['macdinh']): ?><span class="badge text-bg-success ms-2">Mặc định</span><?php endif; ?>
              </div>
              <div class="d-flex gap-2">
                <?php if (!(int)$address['macdinh']): ?>
                  <form method="post" action="index.php">
                    <?= csrf_field() ?><input type="hidden" name="action" value="datdiachimacdinh"><input type="hidden" name="id" value="<?= (int)$address['id'] ?>">
                    <button class="btn btn-sm btn-outline-primary">Đặt mặc định</button>
                  </form>
                <?php endif; ?>
                <form method="post" action="index.php" onsubmit="return confirm('Xóa địa chỉ này?')">
                  <?= csrf_field() ?><input type="hidden" name="action" value="xoadiachi"><input type="hidden" name="id" value="<?= (int)$address['id'] ?>">
                  <button class="btn btn-sm btn-outline-danger">Xóa</button>
                </form>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
      <form method="post" action="index.php" class="border-top pt-3">
        <?= csrf_field() ?><input type="hidden" name="action" value="themdiachi">
        <label class="form-label" for="newAddress">Thêm địa chỉ mới</label>
        <div class="input-group">
          <input id="newAddress" class="form-control" name="diachi" maxlength="255" required placeholder="Số nhà, đường, phường, quận, thành phố">
          <button class="btn btn-outline-primary">Thêm địa chỉ</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include 'inc/bottom.php'; ?>
