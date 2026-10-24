<?php require_once __DIR__ . '/card.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? 'Trang chủ') . ' | ' . cfg('site_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/gg.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-gg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">🥂 GOLDENGATE</a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Thực đơn</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Nhóm món</a>
          <ul class="dropdown-menu">
            <?php foreach ($nhomon as $n): ?>
              <li><a class="dropdown-item" href="index.php?action=group&id=<?= (int)$n['id'] ?>"><?= e($n['tennhom']) ?></a></li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="index.php?action=tracuu">Tra cứu đơn</a></li>
      </ul>
      <form class="d-flex me-3" method="get" action="index.php">
        <input type="hidden" name="action" value="timkiem">
        <input class="form-control form-control-sm me-1" name="q" placeholder="Tìm món..." value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn btn-sm btn-outline-light">Tìm</button>
      </form>
      <ul class="navbar-nav align-items-lg-center">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">📍 <?= e($cb['tenchinhanh'] ?? 'Chọn chi nhánh') ?></a>
          <ul class="dropdown-menu dropdown-menu-end">
            <?php foreach ($chinhanhs as $c): ?>
              <li>
                <form method="post" action="index.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="chonchinhanh">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="back" value="<?= e($here) ?>">
                  <button class="dropdown-item <?= (int)$c['id'] === (int)$cnId ? 'fw-bold' : '' ?>"><?= e($c['tenchinhanh']) ?></button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="index.php?action=giohang">🛒 Giỏ hàng <span class="badge bg-warning text-dark"><?= (int)$soGio ?></span></a></li>
        <?php if ($user): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">👤 <?= e($user['hoten']) ?></a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="index.php?action=donhang">Đơn hàng của tôi</a></li>
              <?php if ((int)$user['loai'] <= 2): ?><li><a class="dropdown-item" href="../admin/index.php">Trang quản trị</a></li><?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="index.php?action=dangxuat">Đăng xuất</a></li>
            </ul>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="index.php?action=dangnhap">Đăng nhập</a></li>
          <li class="nav-item"><a class="btn btn-sm btn-gg ms-lg-2" href="index.php?action=dangky">Đăng ký</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<main class="container py-4">
<?php foreach (take_flash() as [$t, $msg]): ?>
  <div class="alert alert-<?= e($t) ?> alert-dismissible fade show"><?= e($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; ?>
