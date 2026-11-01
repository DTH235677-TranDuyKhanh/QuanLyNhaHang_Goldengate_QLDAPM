<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản trị GoldenGate</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="admin.css" rel="stylesheet">
</head>
<body class="admin-body">
<div class="container-fluid px-0"><div class="row g-0 min-vh-100">
  <aside class="col-md-3 col-lg-2 admin-sidebar">
    <div class="admin-sidebar-inner">
      <a class="admin-brand" href="index.php">
        <span class="admin-brand-mark">G</span>
        <span>GOLDENGATE</span>
      </a>
      <div class="admin-user-card">
        <div class="admin-user-name"><?= e($user['hoten']) ?></div>
        <div class="admin-user-role"><?= $isAdmin ? 'Quản trị toàn hệ thống' : 'Quản lý chi nhánh' ?></div>
      </div>
      <div class="admin-nav-label">Menu chính</div>
      <nav>
    <?php
      $menu = [['dashboard', '📊 Tổng quan', 1], ['donhang', '🧾 Đơn hàng', 1], ['datban', '🪑 Đặt bàn', 1], ['tonkho', '📦 Tồn kho chi nhánh', 1], ['baocao', '📈 Báo cáo', 1],
               ['voucher', '🎟 Voucher', 0],
               ['monan', '🍜 Món ăn', 0], ['slider', '🖼 Slider', 0], ['nhomon', '🗂 Nhóm món', 0], ['chinhanh', '🏬 Chi nhánh', 0], ['nguoidung', '👥 Người dùng', 0]];
      foreach ($menu as [$k, $lb, $all]) { if (!$all && !$isAdmin) continue; ?>
        <a href="index.php?action=<?= $k ?>" class="admin-nav-link <?= $action === $k ? 'active' : '' ?>"><?= $lb ?></a>
    <?php } ?>
      </nav>
      <div class="admin-sidebar-footer">
        <a class="admin-nav-link" href="../public/index.php" target="_blank" rel="noopener">🌐 Xem website</a>
        <a class="admin-nav-link" href="index.php?action=dangxuat">🚪 Đăng xuất</a>
      </div>
    </div>
  </aside>
  <main class="col-md-9 col-lg-10 admin-main">
    <header class="admin-topbar">
      <p class="admin-topbar-label mb-0">Bảng điều khiển</p>
      <span class="admin-topbar-user"><?= e($user['hoten']) ?></span>
    </header>
    <section class="admin-content">
<?php foreach (take_flash() as [$t, $msg]): ?>
  <div class="alert alert-<?= e($t) ?> alert-dismissible fade show"><?= e($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; ?>
