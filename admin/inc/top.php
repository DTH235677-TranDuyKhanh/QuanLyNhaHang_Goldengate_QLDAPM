<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Quản trị GoldenGate</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#f4f1ea}
    .sidebar{background:#1f1a14;min-height:100vh}
    .sidebar a{color:#e9e2d3;text-decoration:none;display:block;padding:.55rem 1rem;border-radius:.4rem}
    .sidebar a:hover,.sidebar a.active{background:#3b2d17;color:#f0c24b}
    .brand{color:#f0c24b;font-weight:700}
    .btn-gg{background:#b8860b;border-color:#b8860b;color:#fff}.btn-gg:hover{background:#8f6808;color:#fff}
  </style>
</head>
<body>
<div class="container-fluid"><div class="row">
  <aside class="col-md-2 sidebar p-3">
    <div class="brand fs-5 mb-1">🥂 GOLDENGATE</div>
    <div class="small text-secondary mb-3"><?= e($user['hoten']) ?><br><?= $isAdmin ? 'Quản trị tổng' : 'Quản lý chi nhánh' ?></div>
    <?php
      $menu = [['dashboard', '📊 Tổng quan', 1], ['donhang', '🧾 Đơn hàng', 1], ['tonkho', '📦 Tồn kho chi nhánh', 1], ['baocao', '📈 Báo cáo', 1],
               ['monan', '🍜 Món ăn', 0], ['nhomon', '🗂 Nhóm món', 0], ['chinhanh', '🏬 Chi nhánh', 0], ['nguoidung', '👥 Người dùng', 0]];
      foreach ($menu as [$k, $lb, $all]) { if (!$all && !$isAdmin) continue; ?>
        <a href="index.php?action=<?= $k ?>" class="<?= $action === $k ? 'active' : '' ?>"><?= $lb ?></a>
    <?php } ?>
    <hr class="border-secondary">
    <a href="../public/index.php" target="_blank">🌐 Xem website</a>
    <a href="index.php?action=dangxuat">🚪 Đăng xuất</a>
  </aside>
  <main class="col-md-10 p-4">
<?php foreach (take_flash() as [$t, $msg]): ?>
  <div class="alert alert-<?= e($t) ?> alert-dismissible fade show"><?= e($msg) ?><button class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endforeach; ?>
