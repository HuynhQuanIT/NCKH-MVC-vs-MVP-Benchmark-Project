<?php
/**
 * ============================================================
 *  SEEDER v3: Giữ 12 sản phẩm thật (qlbh.sql) + thêm 5.000 mock
 * ============================================================
 *  - 12 sản phẩm thật được insert với idProduct = 1..12 (giữ nguyên
 *    tên, giá, ảnh, idType như trong qlbh.sql gốc)
 *  - 5.000 sản phẩm mock được insert tiếp theo (idProduct tự tăng
 *    từ 13), ảnh dùng type_X.svg theo loại
 *  - Tổng cộng: 5.012 bản ghi — vẫn dùng được cho benchmark "~5.000"
 *
 *  Thứ tự chạy:
 *  1. generate_images.php       -> 5 ảnh SVG theo loại (cho mock)
 *  2. generate_real_images.php  -> 12 ảnh JPG cho sản phẩm thật
 *  3. seed_5000_v3.php           -> chèn 12 sp thật + 5.000 sp mock
 *  4. benchmark_real_db.php       -> đo hiệu suất (không đổi)
 * ============================================================
 */

ini_set('max_execution_time', 120);

$conn = mysqli_connect("localhost", "root", "", "qlbh");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

// ── BƯỚC 1: Lấy danh sách idType ──────────────────────────────
$typeIds = [];
$rs = $conn->query("SELECT idType FROM type ORDER BY idType");
if ($rs && $rs->num_rows > 0) {
    while ($row = $rs->fetch_assoc()) $typeIds[] = (int)$row['idType'];
} else {
    $conn->query("INSERT INTO type (idType, typeName) VALUES
        (1,N'Áo Thun'), (2,N'Áo Khoác'), (3,N'Áo Sơ Mi'), (4,N'Áo Polo'), (5,N'Áo Nỉ và Len')");
    $rs = $conn->query("SELECT idType FROM type ORDER BY idType");
    while ($row = $rs->fetch_assoc()) $typeIds[] = (int)$row['idType'];
}
echo "Tìm thấy " . count($typeIds) . " loại sản phẩm: " . implode(', ', $typeIds) . "<br>";

// ── Kiểm tra ảnh đại diện loại (cho mock) đã có chưa ──────────
$missingImg = [];
foreach ($typeIds as $tid) {
    if (!file_exists(__DIR__ . "/MVC/image/type_{$tid}.svg")) $missingImg[] = "type_{$tid}.svg";
}
if (!empty($missingImg)) {
    die("⚠ Thiếu ảnh: " . implode(', ', $missingImg) . ". Hãy chạy <a href='generate_images.php'>generate_images.php</a> trước!");
}

// ── BƯỚC 2: Xóa dữ liệu cũ ────────────────────────────────────
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$conn->query("TRUNCATE TABLE products");
$conn->query("SET FOREIGN_KEY_CHECKS = 1");
echo "Đã xóa dữ liệu cũ trong bảng products.<br>";

// ── BƯỚC 3: Chèn 12 sản phẩm thật từ qlbh.sql (giữ id 1-12) ───
$realProducts = [
    [1, 'Áo Thun Nam Focus Form Boxy', 329000, 299000, 'hinh1.jpg', 1],
    [2, 'Áo Thun Nam Astra Form Boxy', 349000, 319000, 'hinh2.jpg', 1],
    [3, 'Áo Khoác Dù Nam Racing Division Form Loose', 699000, 599000, 'hinh3.jpg', 2],
    [4, 'Áo Khoác Nam Duskpath Form Loose', 699000, 599000, 'hinh4.jpg', 2],
    [5, 'Áo Sơ Mi Nam Cuban Monochrome Mosaic Form Relaxed', 349000, 299000, 'hinh5.jpg', 3],
    [6, 'Áo Sơ Mi Nam Iron Indigo Form Boxy', 379000, 329000, 'hinh6.jpg', 3],
    [7, 'Áo Polo Nam League Form Regular', 349000, 299000, 'hinh7.jpg', 4],
    [8, 'Áo Polo Nam Disney Mickey Grid Form Regular', 429000, 379000, 'hinh8.jpg', 4],
    [9, 'Áo Thun Nam Haritage Form Regular', 290000, 219000, 'o-thun-nam-haritage-form-regular.jpg', 1],
    [10, 'Áo Khoác Varsity Nam Stallion Club Form Regular', 699000, 599000, 'ao-khoac-varsity-nam-stallion-club-form-regular.jpg', 2],
    [11, 'Áo Thun Nam Marvel The Amazing Form Boxy', 399000, 299000, 'ao-thun-nam-marvel-the-amazing-form-boxy.jpg', 1],
    [12, 'Áo Sơ Mi Nam Tay Ngắn Cuban Floral Linen Shirt For Summer Form Relaxed', 349000, 249000, 'ao-so-mi-nam-tay-ngan-cuban-floral-linen-shirt-for-summer-form-relaxed.jpg', 3],
];

$missingRealImg = [];
foreach ($realProducts as $p) {
    if (!file_exists(__DIR__ . "/MVC/image/{$p[4]}")) $missingRealImg[] = $p[4];
}
if (!empty($missingRealImg)) {
    echo "<p style='color:#c00'>⚠ Thiếu ảnh sản phẩm thật: " . implode(', ', $missingRealImg) . ".
          Nên chạy <a href='generate_real_images.php'>generate_real_images.php</a> trước để có ảnh đầy đủ.
          (Vẫn tiếp tục chèn dữ liệu — chỉ ảnh sẽ bị lỗi nếu thiếu.)</p>";
}

$realValues = [];
foreach ($realProducts as $p) {
    $realValues[] = sprintf(
        "(%d, N'%s', %d, %d, '%s', %d)",
        $p[0], $conn->real_escape_string($p[1]), $p[2], $p[3],
        $conn->real_escape_string($p[4]), $p[5]
    );
}
$sqlReal = "INSERT INTO products (idProduct, productName, productPrice, salePrice, image, idType) VALUES "
         . implode(',', $realValues);

if (!$conn->query($sqlReal)) {
    die("Lỗi chèn sản phẩm thật: " . $conn->error);
}
echo "Đã chèn " . count($realProducts) . " sản phẩm thật (idProduct 1-" . count($realProducts) . ").<br>";

// ── BƯỚC 4: Sinh thêm 5.000 sản phẩm mock (idProduct tiếp theo) ──
const MOCK_TOTAL = 5000;
const BATCH      = 500;

srand(42);

$t0 = hrtime(true);
$inserted = 0;

for ($batchStart = 0; $batchStart < MOCK_TOTAL; $batchStart += BATCH) {
    $values = [];
    $limit  = min(BATCH, MOCK_TOTAL - $batchStart);

    for ($i = 0; $i < $limit; $i++) {
        $idx   = $batchStart + $i;
        $type  = $typeIds[$idx % count($typeIds)];
        $name  = "Sản phẩm " . mb_strtoupper(chr(65 + ($idx % 26))) . str_pad($idx, 5, '0', STR_PAD_LEFT);
        $price = 100000 + ($idx * 137) % 900000;
        $sale  = (int)($price * (0.65 + (($idx * 7) % 30) / 100));
        $image = "type_{$type}.svg"; // ảnh đại diện theo loại

        $values[] = sprintf(
            "(N'%s', %d, %d, '%s', %d)",
            $conn->real_escape_string($name),
            $price, $sale,
            $conn->real_escape_string($image),
            $type
        );
    }

    $sql = "INSERT INTO products (productName, productPrice, salePrice, image, idType) VALUES "
         . implode(',', $values);

    if ($conn->query($sql)) {
        $inserted += $limit;
    } else {
        echo "Lỗi insert mock tại batch $batchStart: " . $conn->error . "<br>";
        break;
    }
}

$elapsed = round((hrtime(true) - $t0) / 1e6, 2);

// Đặt lại AUTO_INCREMENT cho an toàn (MariaDB tự cập nhật, nhưng đảm bảo chắc chắn)
$max = $conn->query("SELECT MAX(idProduct) AS m FROM products")->fetch_assoc()['m'];
$conn->query("ALTER TABLE products AUTO_INCREMENT = " . ($max + 1));

echo "<hr>";
echo "<h3>Hoàn tất sinh dữ liệu</h3>";
echo "Sản phẩm thật (giữ nguyên): <b>" . count($realProducts) . "</b><br>";
echo "Sản phẩm mock đã chèn: <b>$inserted / " . MOCK_TOTAL . "</b><br>";
echo "Thời gian sinh mock: <b>$elapsed ms</b><br>";

$check = $conn->query("SELECT COUNT(*) AS total FROM products");
$total = $check->fetch_assoc()['total'];
echo "<b>Tổng số bản ghi trong products: $total</b><br>";

// ── BƯỚC 5: Bảng phân bổ theo loại ─────────────────────────────
$dist = $conn->query("
    SELECT t.idType, t.typeName,
           SUM(CASE WHEN p.idProduct <= 12 THEN 1 ELSE 0 END) AS soSpThat,
           SUM(CASE WHEN p.idProduct > 12 THEN 1 ELSE 0 END)  AS soSpMock,
           COUNT(*) AS tong
    FROM products p
    LEFT JOIN type t ON p.idType = t.idType
    GROUP BY t.idType, t.typeName
    ORDER BY t.idType
");
echo "<h3>Phân bổ theo loại:</h3>";
echo "<table border=1 cellpadding=6 style='border-collapse:collapse'>";
echo "<tr><th>idType</th><th>Tên loại</th><th>SP thật</th><th>SP mock</th><th>Tổng</th></tr>";
while ($row = $dist->fetch_assoc()) {
    echo "<tr><td>{$row['idType']}</td><td>{$row['typeName']}</td>"
       . "<td>{$row['soSpThat']}</td><td>{$row['soSpMock']}</td><td>{$row['tong']}</td></tr>";
}
echo "</table>";

$conn->close();
?>

<p style="margin-top:20px">
  Hoàn tất: <b>12 sản phẩm thật</b> (id 1-12, ảnh JPG thật) + <b><?= $inserted ?> sản phẩm mock</b>
  (id 13+, ảnh SVG theo loại) — tổng <b><?= $total ?? '~5012' ?></b> bản ghi.<br><br>
  • Mở <a href="MVC/index.php">MVC/index.php</a> — 12 sản phẩm đầu danh sách là sản phẩm thật, có ảnh + tên đầy đủ.<br>
  • Click từng danh mục sidebar để kiểm tra sản phẩm thật + mock cùng hiển thị đúng loại.<br>
  • Chạy <a href="benchmark_real_db.php">benchmark_real_db.php</a> để đo hiệu suất trên ~5.012 bản ghi.
</p>
