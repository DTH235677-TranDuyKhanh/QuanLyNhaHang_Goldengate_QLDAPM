INSERT INTO categories (category_id, category_name) VALUES 
(1, 'Món Khai Vị'),
(2, 'Món Chính'),
(3, 'Lẩu'),
(4, 'Nướng'),
(5, 'Đồ Uống');

-- Thêm danh sách món ăn chi tiết cho từng danh mục
INSERT INTO foods (category_id, food_name, price, status, image) VALUES 
-- Món Khai Vị (category_id = 1)
(1, 'Khoai tây chiên giòn', 45000, 'available', 'https://via.placeholder.com/300x200?text=Khoai+Tay+Chien'),
(1, 'Salad trộn dầu giấm', 55000, 'available', 'https://via.placeholder.com/300x200?text=Salad+Tron'),
(1, 'Súp cua trứng cút', 40000, 'available', 'https://via.placeholder.com/300x200?text=Sup+Cua'),

-- Món Chính (category_id = 2)
(2, 'Cơm chiên hải sản đặc biệt', 85000, 'available', 'https://via.placeholder.com/300x200?text=Com+Chien+Hai+San'),
(2, 'Bò bít tết sốt tiêu đen', 150000, 'available', 'https://via.placeholder.com/300x200?text=Bo+Bit+Tet'),
(2, 'Mì ý sốt bò băm', 75000, 'available', 'https://via.placeholder.com/300x200?text=Mi+Y+Bo+Bam'),

-- Lẩu (category_id = 3)
(3, 'Lẩu Thái hải sản chua cay', 299000, 'available', 'https://via.placeholder.com/300x200?text=Lau+Thai'),
(3, 'Lẩu bò nấm nhúng', 320000, 'available', 'https://via.placeholder.com/300x200?text=Lau+Bo'),

-- Nướng (category_id = 4)
(4, 'Sườn heo nướng BBQ', 160000, 'available', 'https://via.placeholder.com/300x200?text=Suon+Nuong+BBQ'),
(4, 'Ba chỉ bò mỹ nướng sốt cay', 140000, 'available', 'https://via.placeholder.com/300x200?text=Ba+Chi+Bo+Nuong'),

-- Đồ Uống (category_id = 5)
(5, 'Coca Cola / Pepsi', 20000, 'available', 'https://via.placeholder.com/300x200?text=Coca+Cola'),
(5, 'Trà đào cam sả', 35000, 'available', 'https://via.placeholder.com/300x200?text=Tra+Dao'),
(5, 'Nước ép cam tươi', 40000, 'sold_out', 'https://via.placeholder.com/300x200?text=Nuoc+Ep+Cam');