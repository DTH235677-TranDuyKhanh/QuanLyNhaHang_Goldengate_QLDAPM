<?php require_once __DIR__ . '/card.php'; ?>
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e(($title ?? 'Trang chủ') . ' | ' . cfg('site_name')) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/gg.css?v=<?= (int)filemtime(__DIR__ . '/../assets/gg.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-xxl navbar-gg navbar-dark sticky-top">
  <div class="container-xl">
    <a class="navbar-brand" href="index.php" aria-label="GoldenGate - Trang chủ">
      <span class="brand-mark">G</span>
      <span class="brand-name"><span>GOLDEN</span><strong>GATE</strong></span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Mở menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="mainNavbar">
      <ul class="navbar-nav main-nav me-xl-3">
        <li class="nav-item"><a class="nav-link" href="index.php">Trang chủ</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">Thực đơn</a>
          <ul class="dropdown-menu nav-menu">
            <?php foreach ($nhomon as $n): ?>
              <li><a class="dropdown-item" href="index.php?action=group&id=<?= (int)$n['id'] ?>"><?= e($n['tennhom']) ?></a></li>
            <?php endforeach; ?>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="index.php">Xem toàn bộ thực đơn</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link" href="index.php?action=datban">Đặt bàn</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?action=sukien">Ưu đãi</a></li>
        <li class="nav-item"><a class="nav-link" href="index.php?action=tracuu">Tra cứu đơn</a></li>
      </ul>

      <form class="nav-search my-3 my-xl-0 me-xl-auto" method="get" action="index.php" role="search">
        <input type="hidden" name="action" value="timkiem">
        <span class="nav-search-icon" aria-hidden="true">⌕</span>
        <input class="form-control" name="q" aria-label="Tìm món ăn" placeholder="Bạn muốn thưởng thức món gì?" value="<?= e($_GET['q'] ?? '') ?>">
        <button class="btn" type="submit">Tìm món</button>
      </form>

      <div class="nav-actions">
        <div class="dropdown">
          <a class="nav-branch dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <span class="branch-pin" aria-hidden="true">⌖</span>
            <span class="branch-copy"><small>CHI NHÁNH</small><strong><?= e($cb['tenchinhanh'] ?? 'Chọn chi nhánh') ?></strong></span>
          </a>
          <ul class="dropdown-menu dropdown-menu-end nav-menu branch-menu">
            <?php foreach ($chinhanhs as $c): ?>
              <li>
                <form method="post" action="index.php">
                  <?= csrf_field() ?>
                  <input type="hidden" name="action" value="chonchinhanh">
                  <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                  <input type="hidden" name="back" value="<?= e($here) ?>">
                  <button class="dropdown-item <?= (int)$c['id'] === (int)$cnId ? 'fw-bold active-branch' : '' ?>">
                    <span><?= e($c['tenchinhanh']) ?></span>
                    <?php if ((int)$c['id'] === (int)$cnId): ?><span class="branch-check">✓</span><?php endif; ?>
                  </button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>
        <a class="nav-cart" href="index.php?action=giohang" aria-label="Giỏ hàng, <?= (int)$soGio ?> món">
          <span class="cart-icon" aria-hidden="true">🛒</span><span>Giỏ hàng</span><span class="cart-count"><?= (int)$soGio ?></span>
        </a>
        <?php if ($user): ?>
          <div class="dropdown">
            <a class="nav-account dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span class="account-avatar" aria-hidden="true">👤</span><span class="account-name"><?= e($user['hoten']) ?></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end nav-menu">
              <li><a class="dropdown-item" href="index.php?action=hoso">Tài khoản & địa chỉ</a></li>
              <li><a class="dropdown-item" href="index.php?action=donhang">Đơn hàng của tôi</a></li>
              <?php if ((int)$user['loai'] <= 2): ?><li><a class="dropdown-item" href="../admin/index.php">Trang quản trị</a></li><?php endif; ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="index.php?action=dangxuat">Đăng xuất</a></li>
            </ul>
          </div>
        <?php else: ?>
          <a class="nav-login" href="index.php?action=dangnhap">Đăng nhập</a>
          <a class="btn nav-register" href="index.php?action=dangky">Đăng ký <span aria-hidden="true">→</span></a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<main class="container py-4">
<?php foreach (take_flash() as [$t, $msg]): ?>
  <div class="alert alert-<?= e($t) ?> alert-dismissible fade show"><?= e($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; ?>
