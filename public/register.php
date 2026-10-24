<?php include 'inc/top.php'; ?>
<div class="row justify-content-center"><div class="col-md-6">
  <div class="card card-body border-0 shadow-sm">
    <h4 class="mb-3">Đăng ký tài khoản</h4>
    <form method="post" action="index.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="xldangky">
      <div class="mb-3"><label class="form-label">Họ tên *</label><input class="form-control" name="hoten" required value="<?= e($f['hoten'] ?? '') ?>"></div>
      <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Số điện thoại *</label><input class="form-control" name="sdt" required pattern="0[0-9]{9}" value="<?= e($f['sdt'] ?? '') ?>"></div>
        <div class="col-md-6 mb-3"><label class="form-label">Email</label><input type="email" class="form-control" name="email" value="<?= e($f['email'] ?? '') ?>"></div>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Mật khẩu *</label><input type="password" class="form-control" name="matkhau" minlength="6" required></div>
        <div class="col-md-6 mb-3"><label class="form-label">Nhập lại mật khẩu *</label><input type="password" class="form-control" name="matkhau2" minlength="6" required></div>
      </div>
      <button class="btn btn-gg w-100">Đăng ký</button>
    </form>
    <p class="small text-muted mt-3 mb-0">Nếu bạn từng đặt món bằng số điện thoại này, lịch sử đơn sẽ được gắn vào tài khoản mới để gợi ý món chính xác hơn.</p>
  </div>
</div></div>
<?php include 'inc/bottom.php'; ?>
