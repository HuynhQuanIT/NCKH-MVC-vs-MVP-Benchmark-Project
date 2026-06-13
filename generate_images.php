<?php
/**
 * ============================================================
 *  SINH ẢNH ĐẠI DIỆN THEO LOẠI SẢN PHẨM (5 ảnh SVG)
 * ============================================================
 *  Mỗi loại sản phẩm (idType) sẽ có 1 ảnh riêng, màu khác nhau,
 *  có chữ tên loại — giúp nhận biết đúng loại khi hiển thị.
 *
 *  Cách dùng:
 *  1. Đặt file này tại thư mục gốc (cùng cấp MVC/, MVP/)
 *  2. Truy cập: http://localhost/NCKH/generate_images.php
 *  3. Script tạo ảnh vào MVC/image/ và MVP/image/
 * ============================================================
 */

$conn = mysqli_connect("localhost", "root", "", "qlbh");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

// Lấy danh sách loại sản phẩm thực tế từ DB
$types = [];
$rs = $conn->query("SELECT idType, typeName FROM type ORDER BY idType");
while ($r = $rs->fetch_assoc()) $types[] = $r;
$conn->close();

if (empty($types)) die("Không có loại sản phẩm nào trong bảng type!");

// Bảng màu theo idType (lặp lại nếu nhiều hơn 8 loại)
$colors = ['#4F87C7','#5BAA7A','#D98F4E','#9B6BC2','#D1597A','#3FA7A0','#C9A227','#7D8597'];

// Thư mục đích — cả MVC và MVP đều có image/
$targets = [
    __DIR__ . '/MVC/image',
    __DIR__ . '/MVP/image',
];

foreach ($targets as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
        echo "Đã tạo thư mục: $dir<br>";
    }
}

$created = 0;
foreach ($types as $i => $t) {
    $idType   = $t['idType'];
    $typeName = $t['typeName'];
    $color    = $colors[$i % count($colors)];
    $filename = "type_{$idType}.svg";

    // SVG đơn giản: nền màu + icon áo + tên loại
    $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
  <rect width="300" height="300" fill="{$color}"/>
  <g transform="translate(150,115)" fill="#ffffff" opacity="0.9">
    <path d="M-45,-40 L-15,-55 L0,-45 L15,-55 L45,-40 L55,-10 L35,0 L35,55 L-35,55 L-35,0 L-55,-10 Z"
          fill="none" stroke="#ffffff" stroke-width="6" stroke-linejoin="round"/>
  </g>
  <text x="150" y="225" font-family="Arial, sans-serif" font-size="22" font-weight="bold"
        fill="#ffffff" text-anchor="middle">{$typeName}</text>
  <text x="150" y="255" font-family="Arial, sans-serif" font-size="13"
        fill="#ffffff" opacity="0.75" text-anchor="middle">Ảnh đại diện danh mục</text>
</svg>
SVG;

    foreach ($targets as $dir) {
        file_put_contents("$dir/$filename", $svg);
    }
    $created++;
    echo "Đã tạo ảnh cho loại [$idType] $typeName -> $filename (màu $color)<br>";
}

echo "<hr><b>Hoàn tất: đã tạo $created ảnh SVG cho " . count($targets) . " thư mục.</b><br>";
echo "<p>Bây giờ chạy lại <a href='seed_5000_v2.php'>seed_5000_v2.php</a> để gán ảnh đúng loại cho 5.000 sản phẩm.</p>";

// Preview
echo "<h3>Xem trước:</h3>";
foreach ($types as $t) {
    $filename = "type_{$t['idType']}.svg";
    echo "<div style='display:inline-block;text-align:center;margin:8px'>";
    echo "<img src='MVC/image/$filename' width='100' height='100'><br>";
    echo "<small>{$t['typeName']} (idType={$t['idType']})</small>";
    echo "</div>";
}
?>
