</main>
<footer class="py-4 mt-5">
  <div class="container small">
    <div class="row">
      <div class="col-md-6"><strong class="text-warning">GOLDENGATE</strong> - Chuỗi nhà hàng ẩm thực Việt<br>Đặt món trực tuyến - nhận tại quán, mang đi hoặc giao tận nơi.</div>
      <div class="col-md-6 text-md-end">
        <?php if (!empty($cb)): ?>📍 <?= e($cb['diachi']) ?><br>☎ <?= e($cb['sodienthoai']) ?> · <?= substr($cb['giomocua'], 0, 5) ?> - <?= substr($cb['giodongcua'], 0, 5) ?><?php endif; ?>
      </div>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
