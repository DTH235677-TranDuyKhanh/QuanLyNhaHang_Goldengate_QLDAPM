<?php include 'inc/top.php'; $img = img_url($m['hinhanh']); $ton = (int)$m['soluongton']; ?>
<div class="row g-4">
  <div class="col-md-5">
    <?php if ($img): ?><img src="<?= e($img) ?>" class="img-fluid rounded-4 shadow-sm" alt="<?= e($m['tenmon']) ?>">
    <?php else: ?><div class="mon-ph rounded-4" style="height:320px;font-size:6rem">🍽️</div><?php endif; ?>
  </div>
  <div class="col-md-7">
    <span class="badge bg-dark mb-2"><?= e($m['tennhom']) ?></span>
    <h2><?= e($m['tenmon']) ?></h2>
    <div class="fs-3 price mb-2"><?= money($m['giaban']) ?></div>
    <p><?= nl2br(e($m['mota'])) ?></p>
    <p class="text-muted small">👁 <?= (int)$m['luotxem'] ?> lượt xem · 🍴 <?= (int)$m['luotgoi'] ?> phần đã gọi</p>
    <?php if (!$m['conphucvu']): ?>
      <div class="alert alert-warning">Chi nhánh <?= e($cb['tenchinhanh']) ?> hiện không phục vụ món này. Hãy chọn chi nhánh khác.</div>
    <?php elseif ($ton <= 0): ?>
      <div class="alert alert-secondary">Món này tạm hết tại chi nhánh đang chọn.</div>
    <?php else: ?>
      <form method="post" action="index.php" class="d-flex gap-2 align-items-center">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="chovaogio">
        <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
        <input type="hidden" name="back" value="<?= e($here) ?>">
        <input type="number" name="soluong" value="1" min="1" max="<?= min(20, $ton) ?>" class="form-control" style="width:90px">
        <button class="btn btn-gg">Thêm vào giỏ hàng</button>
        <?php if ($ton <= 5): ?><span class="text-danger small">Chỉ còn <?= $ton ?> phần</span><?php endif; ?>
      </form>
    <?php endif; ?>
  </div>
</div>
<?php if ($cungNhom): ?>
  <h5 class="section-title mt-5 mb-3">Món cùng nhóm</h5>
  <div class="row g-3"><?php foreach ($cungNhom as $x) echo card_mon($x, $here); ?></div>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
