-- Apply once to an existing GoldenGate database after taking a backup.
USE `goldengate`;

CREATE TABLE sukien (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tieu_de VARCHAR(255) NOT NULL,
  mo_ta TEXT NOT NULL,
  hinh_anh VARCHAR(255) NOT NULL,
  ma_uu_dai VARCHAR(40) DEFAULT NULL,
  bat_dau DATETIME NOT NULL,
  ket_thuc DATETIME NOT NULL,
  chinhanh_id INT DEFAULT NULL,
  trangthai TINYINT NOT NULL DEFAULT 1,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_sukien_public (trangthai, bat_dau, ket_thuc),
  KEY idx_sukien_branch (chinhanh_id),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE danhgia (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nguoidung_id INT NOT NULL,
  donhang_id INT NOT NULL,
  monan_id INT NOT NULL,
  so_sao TINYINT UNSIGNED NOT NULL,
  noi_dung VARCHAR(1000) NOT NULL,
  trangthai TINYINT NOT NULL DEFAULT 1,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_danhgia_order_food (donhang_id, monan_id),
  KEY idx_danhgia_food_status (monan_id, trangthai, ngaytao),
  KEY idx_danhgia_user (nguoidung_id),
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id),
  FOREIGN KEY (donhang_id) REFERENCES donhang(id),
  FOREIGN KEY (monan_id) REFERENCES monan(id),
  CHECK (so_sao BETWEEN 1 AND 5)
) ENGINE=InnoDB;
