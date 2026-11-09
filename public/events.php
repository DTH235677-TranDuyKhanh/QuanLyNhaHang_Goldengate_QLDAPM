<?php include 'inc/top.php'; ?>
<div class="d-flex justify-content-between align-items-end mb-3">
  <div>
    <h2 class="section-title mb-1">Sự kiện và ưu đãi</h2>
    <p class="text-muted mb-0">Thông tin chương trình đang diễn ra tại <?= e($cb['tenchinhanh'] ?? 'GoldenGate') ?>.</p>
  </div>
</div>
<?php if (!$events): ?>
  <div class="alert alert-info">Hiện chưa có sự kiện hoặc ưu đãi đang diễn ra tại chi nhánh này.</div>
<?php else: ?>
  <div class="row g-4">
    <?php foreach ($events as $event): ?>
      <div class="col-md-6 col-xl-4">
        <article class="card h-100 border-0 shadow-sm overflow-hidden">
          <img src="<?= e(img_url_dir($event['hinh_anh'], 'sukien')) ?>" class="card-img-top" alt="<?= e($event['tieu_de']) ?>" style="height:220px;object-fit:cover">
          <div class="card-body">
            <h3 class="h5"><?= e($event['tieu_de']) ?></h3>
            <p class="text-muted small mb-2">
              <?= e($event['tenchinhanh'] ?: 'Toàn hệ thống') ?> ·
              <?= date('d/m/Y H:i', strtotime($event['bat_dau'])) ?> – <?= date('d/m/Y H:i', strtotime($event['ket_thuc'])) ?>
            </p>
            <p class="mb-2"><?= nl2br(e($event['mo_ta'])) ?></p>
            <?php if ($event['ma_uu_dai_hienthi']): ?>
              <div class="alert alert-warning py-2 mb-0">Mã ưu đãi: <strong><?= e($event['ma_uu_dai_hienthi']) ?></strong></div>
            <?php endif; ?>
          </div>
        </article>
      </div>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php include 'inc/bottom.php'; ?>
