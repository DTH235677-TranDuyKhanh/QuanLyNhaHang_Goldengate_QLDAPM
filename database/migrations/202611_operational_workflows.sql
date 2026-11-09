-- Apply once to an existing GoldenGate database after taking a backup.
USE `goldengate`;

ALTER TABLE chinhanh
  ADD COLUMN suc_chua INT UNSIGNED NOT NULL DEFAULT 60 AFTER giodongcua,
  ADD COLUMN thoi_luong_datban_phut SMALLINT UNSIGNED NOT NULL DEFAULT 120 AFTER suc_chua;

ALTER TABLE donhang
  ADD COLUMN phuongthuc_tt ENUM('cod','vnpay') NOT NULL DEFAULT 'cod' AFTER trangthai,
  ADD COLUMN trangthai_tt ENUM('unpaid','pending','paid','failed','expired','manual_review')
    NOT NULL DEFAULT 'unpaid' AFTER phuongthuc_tt,
  ADD COLUMN vnpay_txn_ref VARCHAR(100) DEFAULT NULL AFTER trangthai_tt,
  ADD COLUMN vnpay_transaction_no VARCHAR(100) DEFAULT NULL AFTER vnpay_txn_ref,
  ADD COLUMN vnpay_response_code VARCHAR(10) DEFAULT NULL AFTER vnpay_transaction_no,
  ADD COLUMN thanhtoan_het_han DATETIME DEFAULT NULL AFTER vnpay_response_code,
  ADD COLUMN thanhtoan_luc DATETIME DEFAULT NULL AFTER thanhtoan_het_han,
  ADD UNIQUE KEY uq_donhang_vnpay_ref (vnpay_txn_ref);

ALTER TABLE danhgia
  ADD COLUMN phan_hoi VARCHAR(1000) DEFAULT NULL AFTER noi_dung,
  ADD COLUMN phan_hoi_luc DATETIME DEFAULT NULL AFTER phan_hoi,
  ADD COLUMN phan_hoi_boi INT DEFAULT NULL AFTER phan_hoi_luc,
  ADD CONSTRAINT fk_danhgia_phanhoi_user
    FOREIGN KEY (phan_hoi_boi) REFERENCES nguoidung(id) ON DELETE SET NULL;

ALTER TABLE sukien
  ADD COLUMN voucher_id INT DEFAULT NULL AFTER ma_uu_dai,
  ADD KEY idx_sukien_voucher (voucher_id),
  ADD CONSTRAINT fk_sukien_voucher FOREIGN KEY (voucher_id) REFERENCES voucher(id) ON DELETE SET NULL;

UPDATE sukien s
JOIN voucher v ON v.ma = s.ma_uu_dai
SET s.voucher_id = v.id;

CREATE TABLE donhang_lichsu (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  donhang_id INT NOT NULL,
  trangthai_cu TINYINT DEFAULT NULL,
  trangthai_moi TINYINT NOT NULL,
  nguoidung_id INT DEFAULT NULL,
  ghichu VARCHAR(500) DEFAULT NULL,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_donhang_lichsu_order (donhang_id, ngaytao),
  FOREIGN KEY (donhang_id) REFERENCES donhang(id) ON DELETE CASCADE,
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE datban_lichsu (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  datban_id INT NOT NULL,
  trangthai_cu TINYINT DEFAULT NULL,
  trangthai_moi TINYINT NOT NULL,
  nguoidung_id INT DEFAULT NULL,
  ghichu VARCHAR(500) DEFAULT NULL,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_datban_lichsu_booking (datban_id, ngaytao),
  FOREIGN KEY (datban_id) REFERENCES datban(id) ON DELETE CASCADE,
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE tonkho_lichsu (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  chinhanh_id INT NOT NULL,
  monan_id INT NOT NULL,
  bien_dong INT NOT NULL,
  loai ENUM('nhap_hang','dieu_chinh','ban_hang','hoan_hang') NOT NULL,
  tham_chieu VARCHAR(80) DEFAULT NULL,
  ghi_chu VARCHAR(255) DEFAULT NULL,
  nguoidung_id INT DEFAULT NULL,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_tonkho_lichsu_item (chinhanh_id, monan_id, ngaytao),
  KEY idx_tonkho_lichsu_ref (tham_chieu),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id),
  FOREIGN KEY (monan_id) REFERENCES monan(id),
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id) ON DELETE SET NULL
) ENGINE=InnoDB;
