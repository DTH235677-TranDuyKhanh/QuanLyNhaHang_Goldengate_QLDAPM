<?php include 'inc/top.php'; ?>
<div class="hero p-4 p-md-5 mb-4">
  <h1 class="display-6 fw-bold">Hương vị Việt - Chuẩn nhà hàng</h1>
  <p class="mb-0">Bạn đang xem thực đơn của <strong><?= e($cb['tenchinhanh'] ?? '') ?></strong>. Có thể đổi chi nhánh ở góc trên bên phải.</p>
</div>

<?php if (!empty($goiy['quen'])): ?>
  <div class="d-flex justify-content-between align-items-center mb-2">
    <h4 class="section-title mb-0">Gọi lại món quen</h4>
    <?php if ($donCu): ?>
      <form method="post" action="index.php">
        <?= csrf_field() ?><input type="hidden" name="action" value="datlai"><input type="hidden" name="id" value="<?= (int)$donCu['id'] ?>">
        <button class="btn btn-sm btn-outline-gg">↻ Đặt lại đơn gần nhất (<?= maDon($donCu['id']) ?>)</button>
      </form>
    <?php endif; ?>
  </div>
  <div class="row g-3 mb-4">
    <?php foreach ($goiy['quen'] as $m) echo card_mon($m, $here, $m['ly_do']); ?>
  </div>
<?php endif; ?>

<?php if (!empty($goiy['khampha'])): ?>
  <h4 class="section-title mb-2"><?= $goiy['canhan'] ? 'Có thể bạn sẽ thích' : 'Món được gọi nhiều' ?></h4>
  <?php if (!$goiy['canhan']): ?>
    <p class="text-muted small">Hãy xem và đặt vài món, GoldenGate sẽ gợi ý riêng cho bạn<?= $user ? '' : ' (đăng nhập để lưu gợi ý)' ?>.</p>
  <?php endif; ?>
  <div class="row g-3 mb-4">
    <?php foreach ($goiy['khampha'] as $m) echo card_mon($m, $here, $m['ly_do']); ?>
  </div>
<?php endif; ?>

<?php foreach ($menu as $tenNhom => $mons): ?>
  <h4 class="section-title mt-4 mb-3"><?= e($tenNhom) ?></h4>
  <div class="row g-3">
    <?php foreach ($mons as $m) echo card_mon($m, $here); ?>
  </div>
<?php endforeach; ?>
<?php if (!$menu): ?><div class="alert alert-warning">Chi nhánh này chưa có món phục vụ.</div><?php endif; ?>
<?php include 'inc/bottom.php'; ?>
