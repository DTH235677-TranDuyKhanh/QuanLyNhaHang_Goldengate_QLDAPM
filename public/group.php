<?php include 'inc/top.php'; ?>
<h4 class="section-title mb-3"><?= e($heading) ?></h4>
<div class="row g-3">
  <?php foreach ($mons as $m) echo card_mon($m, $here); ?>
</div>
<?php if (!$mons): ?><div class="alert alert-info">Không có món nào phù hợp tại chi nhánh này.</div><?php endif; ?>
<?php include 'inc/bottom.php'; ?>
