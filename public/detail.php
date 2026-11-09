<?php include 'inc/top.php'; $img = monan_img_url($m); $ton = (int)$m['soluongton']; ?>
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
    <p class="mb-3">
      <?php $ratingAverage = (float)$reviewSummary['diem_tb']; ?>
      <span class="text-warning"><?= str_repeat('★', (int)round($ratingAverage)) . str_repeat('☆', 5 - (int)round($ratingAverage)) ?></span>
      <span class="text-muted small"><?= number_format($ratingAverage, 1) ?>/5 (<?= (int)$reviewSummary['so_luot'] ?> đánh giá)</span>
    </p>
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
<section class="mt-5">
  <h4 class="section-title mb-3">Phản hồi và đánh giá</h4>
  <?php if (!$reviews): ?><p class="text-muted">Món ăn chưa có đánh giá.</p>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($reviews as $review): ?>
        <div class="col-12"><article class="card border-0 shadow-sm"><div class="card-body">
          <div class="d-flex justify-content-between gap-3">
            <strong><?= e($review['hoten']) ?></strong>
            <span class="text-warning text-nowrap"><?= str_repeat('★', (int)$review['so_sao']) . str_repeat('☆', 5 - (int)$review['so_sao']) ?></span>
          </div>
          <p class="mb-1 mt-2"><?= nl2br(e($review['noi_dung'])) ?></p>
          <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['ngaytao'])) ?></small>
          <?php if (!empty($review['phan_hoi'])): ?>
            <div class="mt-3 p-3 bg-light rounded-3">
              <strong class="small">Phản hồi từ nhà hàng</strong>
              <p class="mb-1 mt-1"><?= nl2br(e($review['phan_hoi'])) ?></p>
              <small class="text-muted"><?= date('d/m/Y H:i', strtotime($review['phan_hoi_luc'])) ?></small>
            </div>
          <?php endif; ?>
        </div></article></div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
<?php if ($cungNhom): ?>
  <h5 class="section-title mt-5 mb-3">Món cùng nhóm</h5>
  <div class="row g-3"><?php foreach ($cungNhom as $x) echo card_mon($x, $here); ?></div>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
