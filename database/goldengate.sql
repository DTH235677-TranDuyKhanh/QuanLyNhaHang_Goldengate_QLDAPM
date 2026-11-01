-- GoldenGate - CSDL v1.0
SET NAMES utf8mb4;
DROP DATABASE IF EXISTS `goldengate`;
CREATE DATABASE `goldengate` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `goldengate`;

CREATE TABLE chinhanh (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenchinhanh VARCHAR(100) NOT NULL,
  diachi VARCHAR(255) NOT NULL,
  sodienthoai VARCHAR(15) DEFAULT NULL,
  giomocua TIME NOT NULL DEFAULT '10:00:00',
  giodongcua TIME NOT NULL DEFAULT '22:00:00',
  trangthai TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE nhomon (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tennhom VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE monan (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tenmon VARCHAR(255) NOT NULL,
  mota TEXT,
  giaban DECIMAL(12,0) NOT NULL DEFAULT 0,
  hinhanh VARCHAR(255) DEFAULT NULL,
  nhom_id INT NOT NULL,
  luotxem INT NOT NULL DEFAULT 0,
  luotgoi INT NOT NULL DEFAULT 0,
  trangthai TINYINT NOT NULL DEFAULT 1,
  FOREIGN KEY (nhom_id) REFERENCES nhomon(id)
) ENGINE=InnoDB;

CREATE TABLE monan_chinhanh (
  chinhanh_id INT NOT NULL,
  monan_id INT NOT NULL,
  soluongton INT NOT NULL DEFAULT 0,
  conphucvu TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (chinhanh_id, monan_id),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id),
  FOREIGN KEY (monan_id) REFERENCES monan(id)
) ENGINE=InnoDB;

CREATE TABLE slider (
  id INT AUTO_INCREMENT PRIMARY KEY,
  tieu_de VARCHAR(255) NOT NULL,
  mota TEXT,
  hinh_anh VARCHAR(255) NOT NULL,
  nut_text VARCHAR(100) DEFAULT NULL,
  nut_link VARCHAR(255) DEFAULT '#',
  vitri INT NOT NULL DEFAULT 0,
  trangthai TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- loai: 1 = quản trị tổng, 2 = quản lý chi nhánh, 3 = khách hàng
CREATE TABLE nguoidung (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) DEFAULT NULL,
  sodienthoai VARCHAR(15) NOT NULL,
  matkhau VARCHAR(255) NOT NULL,
  hoten VARCHAR(255) NOT NULL,
  loai TINYINT NOT NULL DEFAULT 3,
  chinhanh_id INT DEFAULT NULL,
  trangthai TINYINT NOT NULL DEFAULT 1,
  dadangky TINYINT NOT NULL DEFAULT 1,
  ngaytao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_sdt (sodienthoai),
  UNIQUE KEY uq_email (email),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id)
) ENGINE=InnoDB;

CREATE TABLE diachi (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nguoidung_id INT NOT NULL,
  diachi VARCHAR(255) NOT NULL,
  macdinh TINYINT NOT NULL DEFAULT 1,
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id)
) ENGINE=InnoDB;

-- hinhthuc: 1 tại quán, 2 mang đi, 3 giao hàng
-- trangthai: 0 chờ xác nhận, 1 đã xác nhận, 2 đang chuẩn bị, 3 hoàn thành, 4 đã hủy
CREATE TABLE donhang (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nguoidung_id INT NOT NULL,
  chinhanh_id INT NOT NULL,
  hinhthuc TINYINT NOT NULL DEFAULT 2,
  hoten_nhan VARCHAR(255) NOT NULL,
  sodt_nhan VARCHAR(15) NOT NULL,
  diachi VARCHAR(255) DEFAULT NULL,
  ngay DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  phigiao DECIMAL(12,0) NOT NULL DEFAULT 0,
  tongtien DECIMAL(12,0) NOT NULL DEFAULT 0,
  trangthai TINYINT NOT NULL DEFAULT 0,
  ghichu VARCHAR(255) DEFAULT NULL,
  KEY idx_nd (nguoidung_id),
  KEY idx_cn_ngay (chinhanh_id, ngay),
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id),
  FOREIGN KEY (chinhanh_id) REFERENCES chinhanh(id)
) ENGINE=InnoDB;

CREATE TABLE donhangct (
  id INT AUTO_INCREMENT PRIMARY KEY,
  donhang_id INT NOT NULL,
  monan_id INT NOT NULL,
  tenmon VARCHAR(255) NOT NULL,
  dongia DECIMAL(12,0) NOT NULL,
  soluong INT NOT NULL DEFAULT 1,
  thanhtien DECIMAL(12,0) NOT NULL,
  KEY idx_dh (donhang_id),
  FOREIGN KEY (donhang_id) REFERENCES donhang(id),
  FOREIGN KEY (monan_id) REFERENCES monan(id)
) ENGINE=InnoDB;

-- Dữ liệu cho cá nhân hóa: lượt xem theo từng người dùng
CREATE TABLE luotxem_nguoidung (
  nguoidung_id INT NOT NULL,
  monan_id INT NOT NULL,
  soluong INT NOT NULL DEFAULT 1,
  lancuoi DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (nguoidung_id, monan_id),
  FOREIGN KEY (nguoidung_id) REFERENCES nguoidung(id),
  FOREIGN KEY (monan_id) REFERENCES monan(id)
) ENGINE=InnoDB;

-- ===== DỮ LIỆU MẪU =====
INSERT INTO chinhanh (id,tenchinhanh,diachi,sodienthoai) VALUES
(1,'GoldenGate Quận 1','12 Lê Lợi, Quận 1, TP. Hồ Chí Minh','0281234567'),
(2,'GoldenGate Hoàn Kiếm','25 Tràng Tiền, Hoàn Kiếm, Hà Nội','0241234567'),
(3,'GoldenGate Hải Châu','88 Bạch Đằng, Hải Châu, Đà Nẵng','0236123456');

INSERT INTO nhomon (id,tennhom) VALUES (1,'Khai vị'),(2,'Món chính'),(3,'Lẩu & Nướng'),(4,'Tráng miệng'),(5,'Đồ uống');

INSERT INTO monan (id,tenmon,mota,giaban,nhom_id) VALUES
(1,'Gỏi cuốn tôm thịt','Cuốn tươi với tôm, thịt luộc, bún và rau thơm, chấm tương đậu phộng.',55000,1),
(2,'Chả giò hải sản','Chiên giòn nhân tôm, mực, cua; ăn kèm rau sống.',65000,1),
(3,'Súp cua trứng bạc','Súp cua sánh mịn, trứng bạc, nấm đông cô.',70000,1),
(4,'Cơm chiên dương châu','Cơm chiên trứng, lạp xưởng, tôm, đậu Hà Lan.',75000,2),
(5,'Bò lúc lắc','Thăn bò Úc xào lửa lớn với hành tây, ớt chuông, sốt tiêu đen.',165000,2),
(6,'Cá hồi sốt chanh dây','Cá hồi Na Uy áp chảo, sốt chanh dây chua ngọt.',220000,2),
(7,'Gà nướng mật ong','Gà ta nướng mật ong, da giòn, thịt mềm.',185000,2),
(8,'Lẩu Thái hải sản','Nước lẩu chua cay, tôm, mực, nghêu, nấm; phục vụ 2-3 người.',389000,3),
(9,'Sườn nướng BBQ','Sườn heo tẩm sốt BBQ, nướng than hoa.',215000,3),
(10,'Bò nướng lá lốt','Bò cuộn lá lốt nướng, chấm mắm nêm.',135000,3),
(11,'Chè hạt sen long nhãn','Chè thanh mát, hạt sen bùi, long nhãn ngọt dịu.',45000,4),
(12,'Bánh flan caramel','Mềm mịn, caramel đắng nhẹ.',35000,4),
(13,'Kem dừa','Kem dừa béo thơm, dừa non, đậu phộng rang.',49000,4),
(14,'Trà đào cam sả','Trà đào, cam vàng, sả tươi.',45000,5),
(15,'Nước ép cam','Cam tươi vắt, không đường thêm.',50000,5),
(16,'Cà phê sữa đá','Cà phê phin truyền thống.',39000,5);

INSERT INTO monan_chinhanh (chinhanh_id, monan_id, soluongton, conphucvu)
SELECT c.id, m.id, 40, 1 FROM chinhanh c CROSS JOIN monan m;

INSERT INTO slider (tieu_de, mota, hinh_anh, nut_text, nut_link, vitri, trangthai) VALUES
('Hương vị Việt - Chuẩn nhà hàng', 'Bạn đang xem thực đơn của <strong>GoldenGate Quận 1</strong>. Có thể đổi chi nhánh ở góc trên bên phải.', 'https://images.unsplash.com/photo-1552566626-52f8b828add9?auto=format&fit=crop&w=1600&q=80', 'Xem thực đơn', '#menu', 1, 1),
('Món ngon, phục vụ đúng chuẩn', 'Không gian ấm cúng, phục vụ nhanh, nguyên liệu tươi mỗi ngày.', 'https://images.unsplash.com/photo-1544025162-d76694265947?auto=format&fit=crop&w=1600&q=80', 'Khám phá', '#menu', 2, 1),
('Trải nghiệm ẩm thực Việt', 'Từ món ăn gia đình đến không gian thưởng thức hiện đại, đều dành cho bạn.', 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1600&q=80', 'Đặt món ngay', '#menu', 3, 1);

-- Mật khẩu mẫu: admin = Admin@123, quản lý = Quanly@123, khách = Khach@123
INSERT INTO nguoidung (id,email,sodienthoai,matkhau,hoten,loai,chinhanh_id) VALUES
(1,'admin@goldengate.vn','0900000001','$2b$10$ZpgtEofKgQuziwoPxBiUfu01T0CzeBITKunc4AtzIfd7wyOpqO.Em','Quản trị tổng',1,NULL),
(2,'quanly1@goldengate.vn','0900000002','$2b$10$WbKamOTDjnZAytHdmOPyFOSjpSLeYXMn7thKzOC2wF5Spz.Zwn7yW','Quản lý Quận 1',2,1),
(3,'khach@goldengate.vn','0900000003','$2b$10$CuDoFs0EsqohIuttyd931u1.UkIxK7fBxtlhIuqbMxw5zIj/B2CDW','Nguyễn Thị Thu An',3,NULL);

INSERT INTO diachi (nguoidung_id,diachi,macdinh) VALUES (3,'45 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',1);

-- Đơn mẫu của khách để minh họa gợi ý cá nhân hóa
INSERT INTO donhang (id,nguoidung_id,chinhanh_id,hinhthuc,hoten_nhan,sodt_nhan,diachi,ngay,phigiao,tongtien,trangthai) VALUES
(1,3,1,3,'Nguyễn Thị Thu An','0900000003','45 Nguyễn Huệ, Quận 1, TP. Hồ Chí Minh',DATE_SUB(NOW(),INTERVAL 20 DAY),15000,435000,3),
(2,3,1,2,'Nguyễn Thị Thu An','0900000003',NULL,DATE_SUB(NOW(),INTERVAL 9 DAY),0,425000,3),
(3,3,1,1,'Nguyễn Thị Thu An','0900000003',NULL,DATE_SUB(NOW(),INTERVAL 2 DAY),0,420000,3);
INSERT INTO donhangct (donhang_id,monan_id,tenmon,dongia,soluong,thanhtien) VALUES
(1,5,'Bò lúc lắc',165000,2,330000),(1,14,'Trà đào cam sả',45000,2,90000),
(2,5,'Bò lúc lắc',165000,1,165000),(2,9,'Sườn nướng BBQ',215000,1,215000),(2,14,'Trà đào cam sả',45000,1,45000),
(3,10,'Bò nướng lá lốt',135000,1,135000),(3,9,'Sườn nướng BBQ',215000,1,215000),(3,12,'Bánh flan caramel',35000,2,70000);
INSERT INTO luotxem_nguoidung (nguoidung_id,monan_id,soluong) VALUES (3,8,3),(3,7,2),(3,6,1);

UPDATE monan m SET luotgoi = (SELECT COALESCE(SUM(soluong),0) FROM donhangct WHERE monan_id=m.id) + ((m.id*7) % 23) + 5;
