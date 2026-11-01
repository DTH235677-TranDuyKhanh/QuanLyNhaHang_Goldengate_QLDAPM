<!DOCTYPE html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Đăng nhập quản trị</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body style="background:#1f1a14">
<div class="container"><div class="row justify-content-center pt-5"><div class="col-md-4">
  <div class="card card-body shadow">
    <h4 class="text-center mb-3">🥂 GoldenGate - Quản trị</h4>
    <?php foreach (take_flash() as [$t, $msg]): ?><div class="alert alert-<?= e($t) ?>"><?= e($msg) ?></div><?php endforeach; ?>
    <form method="post" action="index.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="xldangnhap">
      <div class="mb-3"><label class="form-label">Email hoặc số điện thoại</label><input class="form-control" name="dinhdanh" required autofocus></div>
      <div class="mb-3"><label class="form-label">Mật khẩu</label><input type="password" class="form-control" name="matkhau" required></div>
      <button class="btn btn-warning w-100">Đăng nhập</button>
    </form>
  </div>
</div></div></div></body></html>
