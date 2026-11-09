<?php include 'inc/top.php'; ?>
<?php $slides = $slides ?? []; ?>
<?php
$menuCategoryIds = [];
foreach ($menu as $categoryName => $categoryItems) {
    $menuCategoryIds[$categoryName] = 'menu-category-' . count($menuCategoryIds);
}
?>
<div class="home-page">
  <div class="home-welcome">
    <div>
      <span class="eyebrow"><span class="eyebrow-dot"></span> HƯƠNG VỊ ĐÁNG NHỚ, TRẢI NGHIỆM TRỌN VẸN</span>
      <h2>Chào mừng đến với <span>GoldenGate</span></h2>
      <p>Chọn món ngon, đặt bàn và tận hưởng khoảnh khắc bên những người bạn yêu quý.</p>
    </div>
    <a class="welcome-branch" href="#menu">
      <span class="welcome-pin">⌖</span>
      <span><small>ĐANG PHỤC VỤ TẠI</small><strong><?= e($cb['tenchinhanh'] ?? 'GoldenGate') ?></strong></span>
      <span class="welcome-arrow" aria-hidden="true">↓</span>
    </a>
  </div>

  <?php if ($slides): ?>
    <div id="ggHeroSlider" class="carousel slide hero-slider" data-bs-ride="carousel">
      <div class="carousel-indicators">
        <?php foreach ($slides as $i => $slide): ?>
          <button type="button" data-bs-target="#ggHeroSlider" data-bs-slide-to="<?= (int)$i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= (int)$i + 1 ?>"></button>
        <?php endforeach; ?>
      </div>
      <div class="carousel-inner">
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
            $link = !empty($slide['nut_link']) ? $slide['nut_link'] : (!empty($slide['link']) ? $slide['link'] : '#menu');
          ?>
          <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
            <?php if ($image): ?><img src="<?= e($image) ?>" class="d-block w-100 hero-image" alt="<?= e($title) ?>"><?php endif; ?>
            <div class="carousel-caption">
              <div class="hero-copy">
                <span class="hero-kicker">GOLDENGATE · TINH HOA ẨM THỰC</span>
                <?php if ($title): ?><h1><?= e($title) ?></h1><?php endif; ?>
                <?php if ($text): ?><p><?= $text ?></p><?php endif; ?>
                <?php if ($button): ?><a href="<?= e($link) ?>" class="btn btn-gg hero-button"><?= e($button) ?><span aria-hidden="true">→</span></a><?php endif; ?>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php if (count($slides) > 1): ?>
        <button class="carousel-control-prev" type="button" data-bs-target="#ggHeroSlider" data-bs-slide="prev">
          <span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Trước</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#ggHeroSlider" data-bs-slide="next">
          <span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Tiếp</span>
        </button>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <div class="home-highlights">
    <div class="highlight-item"><span class="highlight-icon">✦</span><span><strong>Món ngon mỗi ngày</strong><small>Nguyên liệu tươi, vị trọn vẹn</small></span></div>
    <div class="highlight-item"><span class="highlight-icon">⌖</span><span><strong>Chọn chi nhánh tiện lợi</strong><small>Thực đơn đúng nơi bạn chọn</small></span></div>
    <div class="highlight-item"><span class="highlight-icon">♡</span><span><strong>Đặt món theo cách của bạn</strong><small>Dùng tại quán, mang đi hoặc giao</small></span></div>
  </div>

  <?php if ($menu): ?>
    <nav class="category-shortcuts" aria-label="Đi nhanh đến nhóm món">
      <span class="shortcut-label">Khám phá thực đơn</span>
      <?php foreach ($menu as $categoryName => $mons): ?>
        <a href="#<?= e($menuCategoryIds[$categoryName]) ?>"><?= e($categoryName) ?></a>
      <?php endforeach; ?>
      <a class="all-menu-link" href="#menu">Tất cả món <span aria-hidden="true">↓</span></a>
    </nav>
  <?php endif; ?>

  <?php if (!empty($goiy['quen'])): ?>
    <section class="menu-section recommendation-section">
      <div class="section-heading">
        <div><span class="section-kicker">DÀNH RIÊNG CHO BẠN</span><h2 class="section-title">Gọi lại món quen</h2></div>
        <?php if ($donCu): ?>
          <form method="post" action="index.php">
            <?= csrf_field() ?><input type="hidden" name="action" value="datlai"><input type="hidden" name="id" value="<?= (int)$donCu['id'] ?>">
            <button class="btn btn-outline-gg reorder-button"><span aria-hidden="true">↻</span> Đặt lại đơn gần nhất</button>
          </form>
        <?php endif; ?>
      </div>
      <div class="row g-3 g-lg-4"><?php foreach ($goiy['quen'] as $m) echo card_mon($m, $here, $m['ly_do']); ?></div>
    </section>
  <?php endif; ?>

  <?php if (!empty($goiy['khampha'])): ?>
    <section class="menu-section">
      <div class="section-heading">
        <div><span class="section-kicker"><?= $goiy['canhan'] ? 'GỢI Ý THEO SỞ THÍCH' : 'ĐƯỢC THỰC KHÁCH YÊU THÍCH' ?></span>
          <h2 class="section-title"><?= $goiy['canhan'] ? 'Có thể bạn sẽ thích' : 'Món được gọi nhiều' ?></h2></div>
      </div>
      <?php if (!$goiy['canhan']): ?>
        <p class="section-description">Khám phá những món được yêu thích tại GoldenGate<?= $user ? '' : ' — đăng nhập để nhận gợi ý phù hợp với bạn hơn' ?>.</p>
      <?php endif; ?>
      <div class="row g-3 g-lg-4"><?php foreach ($goiy['khampha'] as $m) echo card_mon($m, $here, $m['ly_do']); ?></div>
    </section>
  <?php endif; ?>

  <section class="menu-section all-menu-section" id="menu">
    <div class="section-heading menu-heading">
      <div><span class="section-kicker">CHỌN MÓN BẠN YÊU THÍCH</span><h2 class="section-title">Thực đơn GoldenGate</h2></div>
      <a class="menu-booking-link" href="index.php?action=datban">Đặt bàn trước <span aria-hidden="true">→</span></a>
    </div>
    <?php if (!$menu): ?><div class="empty-menu"><span>🍽️</span><strong>Chi nhánh chưa có món phục vụ</strong><p>Vui lòng chọn chi nhánh khác để khám phá thực đơn.</p></div><?php endif; ?>
    <?php foreach ($menu as $categoryName => $mons): ?>
      <section class="category-section" id="<?= e($menuCategoryIds[$categoryName]) ?>">
        <div class="category-heading">
          <h3><?= e($categoryName) ?></h3>
          <span><?= count($mons) ?> món</span>
        </div>
        <div class="row g-3 g-lg-4"><?php foreach ($mons as $m) echo card_mon($m, $here); ?></div>
      </section>
    <?php endforeach; ?>
  </section>
</div>
<?php include 'inc/bottom.php'; ?>
