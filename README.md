# NCKH — MVC vs MVP Benchmark Project

So sánh kiến trúc **MVC** và **MVP** trên cùng ứng dụng quản lý bán hàng PHP,
benchmark hiệu suất trên database thật ở 4 quy mô: **1.000 / 5.000 / 50.000 /
500.000** sản phẩm, tách riêng thời gian **DB** và thời gian **xử lý PHP
thuần** để thấy đúng khác biệt kiến trúc ở mọi quy mô.

---

## 1. Cấu trúc thư mục (rút gọn còn 2 file script)

```
NCKH/
├── MVC/                          # Ứng dụng kiến trúc MVC
├── MVP/                          # Ứng dụng kiến trúc MVP
├── qlbh.sql                      # Schema + 12 sản phẩm thật ban đầu
├── benchmark.php                 # File DUY NHẤT cần chạy: tự sinh ảnh
│                                  # (nếu thiếu) + seed + benchmark 1 mức
│                                  # hoặc cả 4 mức, lưu JSON
├── chart.php                     # File thứ 2: chỉ đọc JSON, vẽ chart
└── results_scalability.json      # Kết quả tích luỹ (tự sinh, đừng sửa tay)
```

> Đã loại bỏ hoàn toàn `seed_lib.php`, `generate_images.php`,
> `generate_real_images.php`, `seed_5000_v3.php`, `benchmark_step.php`,
> `benchmark_chart.php`, `benchmark_real_db.php` (bản cũ) — toàn bộ logic
> đã gộp vào `benchmark.php` + `chart.php`.

---

## 2. Yêu cầu môi trường

- **Laragon** (hoặc XAMPP) — PHP 8.x + MySQL/MariaDB
- Extension **GD** đã bật (Laragon mặc định đã bật) — dùng để sinh ảnh JPG
- Database `qlbh` đã import từ `qlbh.sql` (bảng `type` cần ≥5 dòng: Áo Thun,
  Áo Khoác, Áo Sơ Mi, Áo Polo, Áo Nỉ và Len — idType 1–5)

---

## 3. Build từ đầu — chỉ 2 file

Đặt `benchmark.php` và `chart.php` vào thư mục gốc `NCKH/` (cùng cấp `MVC/`,
`MVP/`, `qlbh.sql`).

### Bước 1 — Benchmark từng mức (hoặc cả 4 mức 1 lần)

```
http://localhost/NCKH/benchmark.php?mock=1000
http://localhost/NCKH/benchmark.php?mock=5000
http://localhost/NCKH/benchmark.php?mock=50000
http://localhost/NCKH/benchmark.php?mock=500000
```
hoặc chạy liên tiếp cả 4 mức trong 1 request:
```
http://localhost/NCKH/benchmark.php?all=1
```

Mỗi lần gọi `benchmark.php`, file tự động:
1. Kiểm tra ảnh trong `MVC/image/`, `MVP/image/` — **chỉ sinh ảnh nào còn
   thiếu** (5 SVG theo loại + 12 JPG sản phẩm thật), bỏ qua nếu đã đủ
2. `TRUNCATE` + re-seed bảng `products` đúng N sản phẩm mock (giữ 12 sp thật
   id 1–12)
3. Chạy 4 bài toán T1 (Tìm kiếm), T2 (Render), T3 (Thống kê), T4 (Phân trang)
   cho cả MVC và MVP, đo bằng `hrtime()` + `gc_collect_cycles()` +
   `memory_get_peak_usage()`
4. Tách 3 chỉ số mỗi bài toán: **full** (tổng) / **DB-only** (chỉ query+fetch)
   / **PHP-only** = full − DB-only (phần xử lý/transform/render thuần PHP —
   nơi MVC vs MVP khác biệt thật, không bị nhiễu bởi MySQL)
5. Append kết quả vào `results_scalability.json` (giữ lại kết quả mức cũ)
6. **DB giữ nguyên ở mức vừa chạy** — mở `MVC/index.php` / `MVP/index.php`
   vẫn thấy đúng N sản phẩm

> Có thể chạy 4 lệnh `?mock=` ở 4 thời điểm khác nhau, không cần liên tiếp.
> `?all=1` tiện nhưng sẽ chạy lâu (mức 500.000 tốn vài phút) và DB sẽ dừng ở
> mức 500.000 sau khi xong. Muốn ép số vòng lặp tuỳ ý: thêm `&iters=100`.

### Bước 2 — Xem biểu đồ

```
http://localhost/NCKH/chart.php
```
Chỉ đọc `results_scalability.json`, **không kết nối DB** → load tức thì.
Hiển thị:
1. Bảng chi tiết full / DB-only / PHP-only theo từng mức
2. Chart xu hướng tổng thời gian (full) — MVC vs MVP
3. Chart xu hướng PHP-only time (đã loại DB) — nơi khác biệt kiến trúc rõ nhất
4. Bar chart % chênh lệch MVP so với MVC theo từng mức (PHP-only)

---

## 4. Kiểm tra ứng dụng

Sau khi chạy `benchmark.php` ở mức bất kỳ:
```
http://localhost/NCKH/MVC/index.php
http://localhost/NCKH/MVP/index.php
```
- 12 sản phẩm đầu là sản phẩm thật (tên đầy đủ, ảnh JPG)
- Còn lại là mock (`Sản phẩm A000000`...), ảnh SVG theo loại
- Click sidebar từng danh mục để kiểm tra lọc theo `idType`

---

## 5. Reset lại từ đầu

1. Xoá `results_scalability.json` nếu muốn benchmark lại sạch từ đầu (không
   bắt buộc — chạy lại 1 mức sẽ tự ghi đè đúng mức đó).
2. Ảnh trong `MVC/image/`, `MVP/image/` không cần xoá — `benchmark.php` chỉ
   sinh lại nếu thiếu. Muốn ép sinh lại toàn bộ ảnh: xoá thư mục `image/` rồi
   chạy `benchmark.php` bất kỳ mức nào.
3. Chạy lại các mức cần qua `benchmark.php?mock=...` rồi mở `chart.php`.
