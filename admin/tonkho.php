<?php include 'inc/top.php'; ?>
<h3 class="mb-3">Tồn kho & thực đơn theo chi nhánh</h3>
<?php if ($isAdmin): ?>
<form method="get" class="mb-3 d-flex gap-2"><input type="hidden" name="action" value="tonkho">
  <select name="cn" class="form-select" style="max-width:320px" onchange="this.form.submit()">
    <?php foreach ($cns as $c): ?><option value="<?= (int)$c['id'] ?>" <?= (int)$c['id'] === (int)$cn ? 'selected' : '' ?>><?= e($c['tenchinhanh']) ?></option><?php endforeach; ?>
  </select></form>
<?php else: ?><p class="text-muted">Chi nhánh: <strong><?= e($cns[0]['tenchinhanh'] ?? '') ?></strong></p><?php endif; ?>
<form method="post" action="index.php"><?= csrf_field() ?><input type="hidden" name="action" value="luutonkho"><input type="hidden" name="cn" value="<?= (int)$cn ?>">
<table class="table table-sm bg-white shadow-sm align-middle">
  <thead><tr><th>Món</th><th>Nhóm</th><th class="text-end">Giá</th><th style="width:140px">Số phần tồn</th><th class="text-center">Phục vụ tại chi nhánh</th></tr></thead>
  <?php foreach ($ds as $m): ?><tr class="<?= (int)$m['soluongton'] <= 5 && $m['conphucvu'] ? 'table-warning' : '' ?>">
    <td><?= e($m['tenmon']) ?></td><td><?= e($m['tennhom']) ?></td><td class="text-end"><?= money($m['giaban']) ?></td>
    <td><input type="number" min="0" class="form-control form-control-sm" name="ton[<?= (int)$m['id'] ?>]" value="<?= (int)$m['soluongton'] ?>"></td>
    <td class="text-center"><input type="checkbox" class="form-check-input" name="phucvu[<?= (int)$m['id'] ?>]" <?= $m['conphucvu'] ? 'checked' : '' ?>></td>
  </tr><?php endforeach; ?>
</table>
<button class="btn btn-gg">Lưu thay đổi</button>
</form>
<?php include 'inc/bottom.php'; ?>
