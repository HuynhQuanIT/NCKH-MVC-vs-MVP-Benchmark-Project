<?php
/**
 * ============================================================
 *  SINH ẢNH THẬT CHO 12 SẢN PHẨM CÓ SẴN TRONG qlbh.sql
 * ============================================================
 *  12 sản phẩm này có tên file ảnh riêng (hinh1.jpg, hinh2.jpg,
 *  ..., ao-thun-nam-marvel-the-amazing-form-boxy.jpg, ...)
 *  nhưng chưa có file ảnh thật -> bị icon ảnh lỗi.
 *
 *  Script này dùng GD (built-in PHP) để tạo ảnh JPG 300x300,
 *  màu theo idType, có chữ tên sản phẩm (rút gọn, không dấu).
 *
 *  Yêu cầu: extension GD phải được bật trong php.ini
 *  (Laragon mặc định đã bật sẵn).
 *
 *  Cách dùng:
 *  1. Đặt file tại thư mục gốc (cùng cấp MVC/, MVP/)
 *  2. Truy cập: http://localhost/NCKH/generate_real_images.php
 * ============================================================
 */

if (!extension_loaded('gd')) {
    die("⚠ Extension GD chưa được bật. Mở php.ini, bỏ comment dòng extension=gd rồi restart Apache.");
}

// ── Danh sách 12 sản phẩm thật từ qlbh.sql (tên file ảnh + idType) ──
$products = [
    ['image' => 'hinh1.jpg', 'label' => 'Ao Thun Focus Boxy',        'idType' => 1],
    ['image' => 'hinh2.jpg', 'label' => 'Ao Thun Astra Boxy',        'idType' => 1],
    ['image' => 'hinh3.jpg', 'label' => 'Ao Khoac Du Racing',        'idType' => 2],
    ['image' => 'hinh4.jpg', 'label' => 'Ao Khoac Duskpath',         'idType' => 2],
    ['image' => 'hinh5.jpg', 'label' => 'Ao So Mi Cuban Mosaic',     'idType' => 3],
    ['image' => 'hinh6.jpg', 'label' => 'Ao So Mi Iron Indigo',      'idType' => 3],
    ['image' => 'hinh7.jpg', 'label' => 'Ao Polo League Regular',    'idType' => 4],
    ['image' => 'hinh8.jpg', 'label' => 'Ao Polo Mickey Grid',       'idType' => 4],
    ['image' => 'o-thun-nam-haritage-form-regular.jpg',                          'label' => 'Ao Thun Haritage',       'idType' => 1],
    ['image' => 'ao-khoac-varsity-nam-stallion-club-form-regular.jpg',           'label' => 'Ao Khoac Varsity',       'idType' => 2],
    ['image' => 'ao-thun-nam-marvel-the-amazing-form-boxy.jpg',                  'label' => 'Ao Thun Marvel',         'idType' => 1],
    ['image' => 'ao-so-mi-nam-tay-ngan-cuban-floral-linen-shirt-for-summer-form-relaxed.jpg', 'label' => 'So Mi Cuban Floral', 'idType' => 3],
];

// Màu theo idType — đồng bộ với generate_images.php
$colors = [
    1 => [0x4F,0x87,0xC7], // Áo Thun
    2 => [0x5B,0xAA,0x7A], // Áo Khoác
    3 => [0xD9,0x8F,0x4E], // Áo Sơ Mi
    4 => [0x9B,0x6B,0xC2], // Áo Polo
    5 => [0xD1,0x59,0x7A], // Áo Nỉ và Len
];

$targets = [__DIR__ . '/MVC/image', __DIR__ . '/MVP/image'];
foreach ($targets as $dir) {
    if (!is_dir($dir)) mkdir($dir, 0777, true);
}

$created = 0;
foreach ($products as $p) {
    $size = 300;
    $img  = imagecreatetruecolor($size, $size);

    $c = $colors[$p['idType']] ?? [0x7D,0x85,0x97];
    $bg = imagecolorallocate($img, $c[0], $c[1], $c[2]);
    imagefill($img, 0, 0, $bg);

    // Viền trắng + icon áo đơn giản (hình thang)
    $white = imagecolorallocate($img, 255, 255, 255);
    $points = [
        110,70,  130,55, 150,65, 170,55, 190,70,
        205,100, 185,110, 185,165, 115,165, 115,110, 95,100,
    ];
    imagepolygon($img, $points, count($points)/2, $white);

    // Text: tên sản phẩm rút gọn (built-in font, ASCII)
    $textColor = imagecolorallocate($img, 255, 255, 255);
    $label = $p['label'];
    $fontW = imagefontwidth(4);
    $textW = $fontW * strlen($label);
    $x = (int)(($size - $textW) / 2);
    imagestring($img, 4, max($x,5), 225, $label, $textColor);
    imagestring($img, 2, max($x,5), 245, "idType={$p['idType']}", $textColor);

    foreach ($targets as $dir) {
        imagejpeg($img, "$dir/{$p['image']}", 85);
    }
    imagedestroy($img);
    $created++;
    echo "Đã tạo: {$p['image']} (idType={$p['idType']})<br>";
}

echo "<hr><b>Hoàn tất: đã tạo $created ảnh JPG cho " . count($targets) . " thư mục.</b><br>";

// Preview
echo "<h3>Xem trước:</h3>";
foreach ($products as $p) {
    echo "<div style='display:inline-block;text-align:center;margin:6px'>";
    echo "<img src='MVC/image/{$p['image']}' width='80' height='80'><br>";
    echo "<small>{$p['image']}</small>";
    echo "</div>";
}

echo "<p style='margin-top:16px'>
  Tiếp theo, chạy <a href='seed_5000_v3.php'>seed_5000_v3.php</a>
  để chèn 12 sản phẩm thật này (giữ nguyên) + 5.000 sản phẩm mock.
</p>";
