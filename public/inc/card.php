<?php
// Thẻ món ăn dùng chung. $m: dòng món (có soluongton), $here: URL hiện tại để quay lại, $lyDo: lý do gợi ý (tùy chọn)
function card_mon($m, $here, $lyDo = null) {
    $img = img_url($m['hinhanh']);
    $ton = (int)($m['soluongton'] ?? 0);
    ob_start(); ?>
    <div class="col-6 col-md-4 col-lg-3">
      <div class="card card-mon">
        <a href="index.php?action=detail&id=<?= (int)$m['id'] ?>" class="text-decoration-none">
          <?php if ($img): ?><img class="mon-img" src="<?= e($img) ?>" alt="<?= e($m['tenmon']) ?>">
          <?php else: ?><div class="mon-ph">🍽️</div><?php endif; ?>
        </a>
        <div class="card-body d-flex flex-column">
          <?php if ($lyDo): ?><span class="badge badge-reason mb-2 align-self-start"><?= e($lyDo) ?></span><?php endif; ?>
          <a href="index.php?action=detail&id=<?= (int)$m['id'] ?>" class="text-dark text-decoration-none fw-semibold"><?= e($m['tenmon']) ?></a>
          <div class="price my-1"><?= money($m['giaban']) ?></div>
          <div class="mt-auto">
            <?php if ($ton <= 0): ?>
              <button class="btn btn-secondary btn-sm w-100" disabled>Tạm hết</button>
            <?php else: ?>
              <form method="post" action="index.php">
                <?= csrf_field() ?>
                <input type="hidden" name="action" value="chovaogio">
                <input type="hidden" name="id" value="<?= (int)$m['id'] ?>">
                <input type="hidden" name="soluong" value="1">
                <input type="hidden" name="back" value="<?= e($here) ?>">
                <button class="btn btn-gg btn-sm w-100">+ Thêm vào giỏ</button>
              </form>
              <?php if ($ton <= 5): ?><small class="text-danger">Chỉ còn <?= $ton ?> phần</small><?php endif; ?>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
    <?php return ob_get_clean();
}
