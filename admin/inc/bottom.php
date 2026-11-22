    </section>
  </main>
</div></div>
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="logoutConfirmLabel">Xác nhận đăng xuất</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Hủy"></button>
      </div>
      <div class="modal-body">
        Bạn có chắc chắn muốn đăng xuất khỏi trang quản trị?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
        <a id="logoutConfirmButton" href="index.php?action=dangxuat" class="btn btn-danger">Đăng xuất</a>
      </div>
    </div>
  </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const logoutModal = document.getElementById('logoutConfirmModal');
  if (logoutModal) {
    logoutModal.addEventListener('show.bs.modal', (event) => {
      const trigger = event.relatedTarget;
      const url = trigger && trigger.dataset.logoutUrl;
      const confirmButton = document.getElementById('logoutConfirmButton');
      if (url && confirmButton) confirmButton.href = url;
    });
  }
</script>
</body></html>
