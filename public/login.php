<?php include 'inc/top.php'; ?>
<div class="row justify-content-center"><div class="col-md-5">
  <div class="card card-body border-0 shadow-sm">
    <h4 class="mb-3">Đăng nhập</h4>
    <form method="post" action="index.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="xldangnhap">
      <div class="mb-3"><label class="form-label">Email hoặc số điện thoại</label><input class="form-control" name="dinhdanh" required autofocus></div>
      <div class="mb-3"><label class="form-label">Mật khẩu</label><input type="password" class="form-control" name="matkhau" required></div>
      <button class="btn btn-gg w-100">Đăng nhập</button>
    </form>
    <p class="small mt-3 mb-0">Chưa có tài khoản? <a href="index.php?action=dangky">Đăng ký</a></p>
  </div>
</div></div>
<?php include 'inc/bottom.php'; ?>
