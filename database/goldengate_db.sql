CREATE DATABASE goldengate DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE goldengate;

CREATE TABLE chinhanh (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenchinhanh VARCHAR(100) NOT NULL,
  diachi VARCHAR(255) NOT NULL,
  sodienthoai VARCHAR(15),
  giomocua TIME DEFAULT '10:00:00',
  giodongcua TIME DEFAULT '22:00:00',
  trangthai TINYINT NOT NULL DEFAULT 1
);

CREATE TABLE nhomon (            -- trước là danhmuc
  id INT AUTO_INCREMENT PRIMARY KEY,
  tennhom VARCHAR(50) NOT NULL
);

CREATE TABLE monan (             -- trước là mathang
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenmon VARCHAR(255) NOT NULL,
  mota TEXT,
  giaban DECIMAL(12,0) NOT NULL DEFAULT 0,
  hinhanh VARCHAR(255),
  nhom_id INT NOT NULL,
  luotxem INT NOT NULL DEFAULT 0,
  luotgoi INT NOT NULL DEFAULT 0,
  trangthai TINYINT NOT NULL DEFAULT 1,
  FOREIGN KEY (nhom_id) REFERENCES nhomon(id)
);

-- Mỗi chi nhánh có tồn kho/phục vụ riêng cho từng món
CREATE TABLE monan_chinhanh (
  chinhanh_id INT NOT NULL,
  monan_id INT NOT NULL,
  soluongton INT NOT NULL DEFAULT 0,
  conphucvu TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (chinhanh_id, monan_id),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id),
  FOREIGN KEY (monan_id) REFERENCES monan(id)
);