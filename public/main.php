<?php include 'inc/top.php'; ?>
<?php
$slides = $slides ?? [];
?>
<div id="ggHeroSlider" class="carousel slide hero-slider mb-4" data-bs-ride="carousel">
  <div class="carousel-indicators">
    <?php foreach ($slides as $i => $slide): ?>
      <button type="button" data-bs-target="#ggHeroSlider" data-bs-slide-to="<?= (int)$i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= (int)$i + 1 ?>"></button>
    <?php endforeach; ?>
  </div>

  <div class="carousel-inner rounded-4 overflow-hidden">
    <?php foreach ($slides as $i => $slide): ?>
      <?php
        $rawImage = !empty($slide['hinh_anh']) ? $slide['hinh_anh'] : (!empty($slide['image']) ? $slide['image'] : '');
        $image = $rawImage;
        if ($image && !preg_match('/^https?:\/\//i', $image) && !preg_match('/^\//', $image)) {
            $image = '../uploads/slider/' . rawurlencode(basename($image));
        }
        $title = !empty($slide['tieu_de']) ? $slide['tieu_de'] : (!empty($slide['title']) ? $slide['title'] : '');
        $text = !empty($slide['mota']) ? $slide['mota'] : (!empty($slide['text']) ? $slide['text'] : '');
        $button = !empty($slide['nut_text']) ? $slide['nut_text'] : (!empty($slide['button']) ? $slide['button'] : '');
        $link = !empty($slide['nut_link']) ? $slide['nut_link'] : (!empty($slide['link']) ? $slide['link'] : '#');
      ?>
      <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
        <?php if ($image): ?><img src="<?= e($image) ?>" class="d-block w-100 hero-image" alt="<?= e($title) ?>"><?php endif; ?>
        <div class="carousel-caption d-flex flex-column justify-content-center align-items-start text-start">
          <?php if ($title): ?><h1 class="display-6 fw-bold mb-2"><?= $title ?></h1><?php endif; ?>
          <?php if ($text): ?><p class="mb-0"><?= $text ?></p><?php endif; ?>
          <?php if ($button): ?><a href="<?= e($link) ?>" class="btn btn-gg mt-3"><?= e($button) ?></a><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <button class="carousel-control-prev" type="button" data-bs-target="#ggHeroSlider" data-bs-slide="prev">
    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Previous</span>
  </button>
  <button class="carousel-control-next" type="button" data-bs-target="#ggHeroSlider" data-bs-slide="next">
    <span class="carousel-control-next-icon" aria-hidden="true"></span>
    <span class="visually-hidden">Next</span>
  </button>
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
