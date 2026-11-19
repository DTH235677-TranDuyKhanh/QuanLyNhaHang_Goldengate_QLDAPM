<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Đăng nhập quản trị | GoldenGate</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin.css?v=<?= (int)filemtime(__DIR__ . '/admin.css') ?>" rel="stylesheet">
</head>
<body class="admin-login-body">
  <main class="admin-login-card">
    <div class="admin-login-brand" aria-hidden="true">🥂</div>
    <h1 class="admin-login-title text-center mb-1">GoldenGate Admin</h1>
    <p class="admin-login-subtitle text-center mb-4">Đăng nhập để quản lý nhà hàng</p>
    <?php foreach (take_flash() as [$t, $msg]): ?><div class="alert alert-<?= e($t) ?>"><?= e($msg) ?></div><?php endforeach; ?>
    <form method="post" action="index.php">
      <?= csrf_field() ?><input type="hidden" name="action" value="xldangnhap">
      <div class="mb-3">
        <label class="form-label" for="dinhdanh">Email hoặc số điện thoại</label>
        <input id="dinhdanh" class="form-control" name="dinhdanh" autocomplete="username" required autofocus>
      </div>
      <div class="mb-4">
        <label class="form-label" for="matkhau">Mật khẩu</label>
        <input id="matkhau" type="password" class="form-control" name="matkhau" autocomplete="current-password" required>
      </div>
      <button class="btn btn-gg w-100 py-2">Đăng nhập</button>
    </form>
  </main>
</body>
</html>
