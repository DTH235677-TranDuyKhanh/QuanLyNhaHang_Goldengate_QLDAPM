-- Apply once to an existing GoldenGate database after taking a backup.
USE `goldengate`;

CREATE TABLE datban (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nguoidung_id INT DEFAULT NULL,
  chinhanh_id INT NOT NULL,
  hoten VARCHAR(255) NOT NULL,
  sodienthoai VARCHAR(15) NOT NULL,
  thoi_gian DATETIME NOT NULL,
  so_khach TINYINT UNSIGNED NOT NULL,
  ghichu VARCHAR(500) DEFAULT NULL,
  trangthai TINYINT NOT NULL DEFAULT 0,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_datban_cn_time (chinhanh_id, thoi_gian),
  KEY idx_datban_user (nguoidung_id),
  KEY idx_datban_status (trangthai),
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id) ON DELETE SET NULL,
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id)
) ENGINE=InnoDB;

CREATE TABLE voucher (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ma VARCHAR(40) NOT NULL,
  loai ENUM('phantram','tienmat') NOT NULL,
  giatri DECIMAL(12,2) NOT NULL,
  don_toi_thieu DECIMAL(12,0) NOT NULL DEFAULT 0,
  giam_toi_da DECIMAL(12,0) DEFAULT NULL,
  bat_dau DATETIME NOT NULL,
  ket_thuc DATETIME NOT NULL,
  gioi_han INT UNSIGNED DEFAULT NULL,
  da_dung INT UNSIGNED NOT NULL DEFAULT 0,
  chinhanh_id INT DEFAULT NULL,
  trangthai TINYINT NOT NULL DEFAULT 1,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_voucher_ma (ma),
  KEY idx_voucher_active_dates (trangthai, bat_dau, ket_thuc),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id) ON DELETE SET NULL
) ENGINE=InnoDB;

ALTER TABLE donhang
  ADD COLUMN voucher_id INT DEFAULT NULL AFTER phigiao,
  ADD COLUMN giamgia DECIMAL(12,0) NOT NULL DEFAULT 0 AFTER voucher_id,
  ADD CONSTRAINT fk_donhang_voucher
    FOREIGN KEY (voucher_id) REFERENCES voucher(id) ON DELETE SET NULL;
