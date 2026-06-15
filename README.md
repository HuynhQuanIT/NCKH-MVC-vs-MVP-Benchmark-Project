# NCKH — MVC vs MVP Benchmark Project

So sánh kiến trúc **MVC** và **MVP** trên cùng một ứng dụng quản lý bán hàng PHP,
kèm bộ dữ liệu mẫu ~5.012 sản phẩm (12 sản phẩm thật + 5.000 sản phẩm mock)
và script benchmark đo hiệu suất thực thi trên database thật.

---

## 1. Cấu trúc thư mục

```
NCKH/
├── MVC/                      # Phiên bản kiến trúc MVC
│   ├── model/
│   ├── controller/
│   ├── view/
│   ├── image/                # Ảnh sản phẩm (sinh tự động)
│   └── index.php / admin.php
├── MVP/                      # Phiên bản kiến trúc MVP
│   ├── model/
│   ├── presenter/
│   ├── view/
│   ├── image/                # Ảnh sản phẩm (sinh tự động)
│   └── index.php / admin.php
├── generate_images.php        # Bước 1 — sinh ảnh SVG theo loại (cho mock)
├── generate_real_images.php    # Bước 2 — sinh ảnh JPG cho 12 sp thật
├── seed_5000_v3.php             # Bước 3 — seed database (5.012 sản phẩm)
└── benchmark_real_db.php         # Bước 4 — đo hiệu suất MVC vs MVP
```

---

## 2. Yêu cầu môi trường

- **Laragon** (hoặc XAMPP) — PHP 8.x + MySQL/MariaDB
- Extension **GD** đã được bật (Laragon mặc định đã bật)
- Database `qlbh` đã được tạo với schema gồm 4 bảng: `products`, `type`, `role`, `user`

> Nếu chưa có schema, import file `qlbh.sql` (bảng `type` cần có ít nhất 5 dòng:
> Áo Thun, Áo Khoác, Áo Sơ Mi, Áo Polo, Áo Nỉ và Len — idType 1–5) trước khi seed.

---

## 3. Hướng dẫn Build (build lại từ đầu)

Đặt 4 file script vào thư mục gốc `NCKH/` (cùng cấp `MVC/`, `MVP/`), sau đó truy cập
theo **đúng thứ tự** sau bằng trình duyệt:

### Bước 1 — Sinh ảnh đại diện theo loại (cho 5.000 sp mock)
```
http://localhost/NCKH/generate_images.php
```
Tạo 5 ảnh SVG (`type_1.svg` ... `type_5.svg`), mỗi ảnh có màu riêng + tên loại,
lưu vào cả `MVC/image/` và `MVP/image/`.

### Bước 2 — Sinh ảnh thật cho 12 sản phẩm gốc
```
http://localhost/NCKH/generate_real_images.php
```
Dùng GD tạo 12 ảnh JPG (300×300) đúng tên file trong `qlbh.sql`
(`hinh1.jpg` ... `hinh8.jpg`, và các tên file dài như
`ao-thun-nam-marvel-the-amazing-form-boxy.jpg`), màu nền theo `idType`,
có chữ tên sản phẩm (rút gọn, không dấu).

> ⚠ Nếu báo lỗi "Extension GD chưa được bật" → mở `php.ini`,
> bỏ comment dòng `extension=gd`, restart Apache trong Laragon.

### Bước 3 — Seed database
```
http://localhost/NCKH/seed_5000_v3.php
```
- Xóa toàn bộ dữ liệu cũ trong bảng `products`
- Chèn lại **12 sản phẩm thật** (id 1–12, giữ nguyên tên/giá/ảnh từ `qlbh.sql`)
- Sinh thêm **5.000 sản phẩm mock** (id 13–5012), ảnh theo loại (`type_X.svg`)
- In bảng phân bổ số lượng sản phẩm theo từng loại để kiểm tra

Kết quả mong đợi: tổng **5.012 bản ghi** trong `products`.

### Bước 4 (tuỳ chọn) — Benchmark hiệu suất MVC vs MVP
```
http://localhost/NCKH/benchmark_real_db.php
```
Chạy 4 bài toán (Tìm kiếm, Render, Thống kê, Phân trang) trên dữ liệu thật,
100 vòng lặp/bài toán, đo bằng `hrtime()` + `gc_collect_cycles()` +
`memory_get_peak_usage()`. Kết quả hiển thị bảng so sánh MVC vs MVP
(µs/request, peak memory) và so sánh với kết quả benchmark mock (không DB).

---

## 4. Kiểm tra kết quả

Mở 1 trong 2 ứng dụng:
```
http://localhost/NCKH/MVC/index.php
http://localhost/NCKH/MVP/index.php
```

- 12 sản phẩm đầu danh sách là **sản phẩm thật** (tên đầy đủ tiếng Việt, ảnh JPG có chữ)
- 5.000 sản phẩm còn lại là **mock** (`Sản phẩm A00000`, `B00001`, ...), ảnh SVG theo loại
- Click từng danh mục ở sidebar (Áo Thun, Áo Khoác...) để kiểm tra lọc theo `idType`
  hiển thị đúng cả sản phẩm thật và mock thuộc loại đó

---

## 5. Build lại từ đầu (reset toàn bộ)

Chạy lại đúng thứ tự **Bước 1 → 2 → 3** (Bước 4 tuỳ chọn). `seed_5000_v3.php` luôn
`TRUNCATE` bảng `products` trước khi chèn lại, nên có thể chạy lại bao nhiêu lần
cũng cho kết quả giống nhau (dữ liệu mock dùng `srand(42)` — tái lập được).

> Không cần chạy lại Bước 1 và 2 nếu ảnh đã tồn tại trong `MVC/image/` và `MVP/image/`
> — chỉ cần chạy lại Bước 3 để reset dữ liệu sản phẩm.

