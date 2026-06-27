<?php
/**
 * ============================================================
 *  benchmark.php — TẤT CẢ TRONG 1 FILE
 * ============================================================
 *  Gộp: generate_images.php + generate_real_images.php +
 *       seed_lib.php + benchmark_step.php (cũ, 5 file riêng)
 *
 *  Tự động:
 *   1. Sinh ảnh SVG theo loại + ảnh JPG 12 sp thật (CHỈ 1 LẦN,
 *      bỏ qua nếu ảnh đã tồn tại — không tốn thời gian các lần sau)
 *   2. Re-seed bảng products đúng mức N (giữ 12 sp thật + N mock)
 *   3. Benchmark 4 bài toán (T1-T4), tách full / DB-only / PHP-only
 *   4. Lưu/append kết quả vào results_scalability.json
 *
 *  Cách dùng:
 *    benchmark.php?mock=1000              -> 1 mức, DB giữ nguyên mức đó
 *    benchmark.php?mock=5000
 *    benchmark.php?mock=50000
 *    benchmark.php?mock=500000
 *    benchmark.php?all=1                  -> chạy lần lượt cả 4 mức
 *                                             trong 1 request (DB sẽ
 *                                             dừng lại ở mức 500000
 *                                             sau khi xong)
 *
 *  Xem chart: chart.php (chỉ đọc JSON, không đụng DB)
 * ============================================================
 */

ini_set('max_execution_time', 0);
ini_set('memory_limit', '2048M');

const RESULTS_FILE = __DIR__ . '/results_scalability.json';
const IMG_DIRS      = [__DIR__ . '/MVC/image', __DIR__ . '/MVP/image'];
const SCALES_DEFAULT = [1000 => 60, 5000 => 40, 50000 => 15, 500000 => 5];

const REAL_PRODUCTS = [
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

$conn = mysqli_connect("localhost", "root", "", "qlbh");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

// ════════════════════════════════════════════════════════════
// 1. ẢNH — chỉ sinh nếu còn THIẾU, bỏ qua nếu đã đủ (tiết kiệm thời gian)
// ════════════════════════════════════════════════════════════
function get_type_ids(mysqli $conn): array {
    $typeIds = [];
    $rs = $conn->query("SELECT idType FROM type ORDER BY idType");
    if ($rs && $rs->num_rows > 0) {
        while ($row = $rs->fetch_assoc()) $typeIds[] = (int)$row['idType'];
        return $typeIds;
    }
    $conn->query("INSERT INTO type (idType, typeName) VALUES
        (1,N'Áo Thun'), (2,N'Áo Khoác'), (3,N'Áo Sơ Mi'), (4,N'Áo Polo'), (5,N'Áo Nỉ và Len')");
    $rs = $conn->query("SELECT idType FROM type ORDER BY idType");
    while ($row = $rs->fetch_assoc()) $typeIds[] = (int)$row['idType'];
    return $typeIds;
}

function ensure_images(mysqli $conn, array $typeIds): void {
    foreach (IMG_DIRS as $dir) if (!is_dir($dir)) mkdir($dir, 0777, true);

    // 1a. Ảnh SVG theo loại (cho mock) — chỉ tạo file nào còn thiếu
    $typeNames = [];
    $rs = $conn->query("SELECT idType, typeName FROM type ORDER BY idType");
    while ($r = $rs->fetch_assoc()) $typeNames[(int)$r['idType']] = $r['typeName'];

    $colors = ['#4F87C7','#5BAA7A','#D98F4E','#9B6BC2','#D1597A','#3FA7A0','#C9A227','#7D8597'];
    $i = 0;
    foreach ($typeNames as $idType => $typeName) {
        $filename = "type_{$idType}.svg";
        $missing = false;
        foreach (IMG_DIRS as $dir) if (!file_exists("$dir/$filename")) $missing = true;
        if ($missing) {
            $color = $colors[$i % count($colors)];
            $svg = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="300" height="300" viewBox="0 0 300 300">
  <rect width="300" height="300" fill="{$color}"/>
  <g transform="translate(150,115)" fill="#ffffff" opacity="0.9">
    <path d="M-45,-40 L-15,-55 L0,-45 L15,-55 L45,-40 L55,-10 L35,0 L35,55 L-35,55 L-35,0 L-55,-10 Z"
          fill="none" stroke="#ffffff" stroke-width="6" stroke-linejoin="round"/>
  </g>
  <text x="150" y="225" font-family="Arial, sans-serif" font-size="22" font-weight="bold" fill="#ffffff" text-anchor="middle">{$typeName}</text>
  <text x="150" y="255" font-family="Arial, sans-serif" font-size="13" fill="#ffffff" opacity="0.75" text-anchor="middle">Ảnh đại diện danh mục</text>
</svg>
SVG;
            foreach (IMG_DIRS as $dir) file_put_contents("$dir/$filename", $svg);
            echo "Đã tạo ảnh SVG: $filename<br>";
        }
        $i++;
    }

    // 1b. Ảnh JPG cho 12 sp thật — chỉ tạo nếu còn thiếu, cần GD
    $missingReal = false;
    foreach (REAL_PRODUCTS as $p) {
        foreach (IMG_DIRS as $dir) if (!file_exists("$dir/{$p[4]}")) $missingReal = true;
    }
    if ($missingReal) {
        if (!extension_loaded('gd')) {
            echo "<p style='color:#c00'>⚠ Thiếu ảnh JPG sp thật và extension GD chưa bật — bỏ qua bước sinh ảnh thật (ảnh sẽ lỗi nhưng không cản benchmark).</p>";
        } else {
            $colorsReal = [1=>[0x4F,0x87,0xC7], 2=>[0x5B,0xAA,0x7A], 3=>[0xD9,0x8F,0x4E], 4=>[0x9B,0x6B,0xC2], 5=>[0xD1,0x59,0x7A]];
            foreach (REAL_PRODUCTS as $p) {
                $img = imagecreatetruecolor(300, 300);
                $c = $colorsReal[$p[5]] ?? [0x7D,0x85,0x97];
                $bg = imagecolorallocate($img, $c[0], $c[1], $c[2]);
                imagefill($img, 0, 0, $bg);
                $white = imagecolorallocate($img, 255, 255, 255);
                imagepolygon($img, [110,70, 130,55, 150,65, 170,55, 190,70, 205,100, 185,110, 185,165, 115,165, 115,110, 95,100], 11, $white);
                $label = preg_replace('/[^A-Za-z0-9 ]/', '', $p[1]);
                imagestring($img, 4, 10, 225, substr($label, 0, 30), $white);
                imagestring($img, 2, 10, 245, "idType={$p[5]}", $white);
                foreach (IMG_DIRS as $dir) imagejpeg($img, "$dir/{$p[4]}", 85);
                imagedestroy($img);
            }
            echo "Đã tạo " . count(REAL_PRODUCTS) . " ảnh JPG sản phẩm thật.<br>";
        }
    }
}

// ════════════════════════════════════════════════════════════
// 2. SEED — re-seed bảng products đúng N mock
// ════════════════════════════════════════════════════════════
function seed_to_total(mysqli $conn, int $mockTotal, array $typeIds, int $batch = 2000): array {
    $t0 = hrtime(true);
    $conn->query("SET FOREIGN_KEY_CHECKS = 0");
    $conn->query("SET UNIQUE_CHECKS = 0");
    $conn->query("SET AUTOCOMMIT = 0");
    $conn->query("TRUNCATE TABLE products");

    $realValues = [];
    foreach (REAL_PRODUCTS as $p) {
        $realValues[] = sprintf("(%d, N'%s', %d, %d, '%s', %d)", $p[0], $conn->real_escape_string($p[1]), $p[2], $p[3], $conn->real_escape_string($p[4]), $p[5]);
    }
    $conn->query("INSERT INTO products (idProduct, productName, productPrice, salePrice, image, idType) VALUES " . implode(',', $realValues));

    srand(42);
    $inserted = 0;
    $numTypes = count($typeIds);
    for ($batchStart = 0; $batchStart < $mockTotal; $batchStart += $batch) {
        $limit = min($batch, $mockTotal - $batchStart);
        $values = [];
        for ($i = 0; $i < $limit; $i++) {
            $idx = $batchStart + $i;
            $type = $typeIds[$idx % $numTypes];
            $name = "Sản phẩm " . mb_strtoupper(chr(65 + ($idx % 26))) . str_pad($idx, 6, '0', STR_PAD_LEFT);
            $price = 100000 + ($idx * 137) % 900000;
            $sale = (int)($price * (0.65 + (($idx * 7) % 30) / 100));
            $image = "type_{$type}.svg";
            $values[] = sprintf("(N'%s', %d, %d, '%s', %d)", $conn->real_escape_string($name), $price, $sale, $conn->real_escape_string($image), $type);
        }
        $conn->query("INSERT INTO products (productName, productPrice, salePrice, image, idType) VALUES " . implode(',', $values));
        $inserted += $limit;
    }

    $conn->query("COMMIT");
    $conn->query("SET AUTOCOMMIT = 1");
    $conn->query("SET UNIQUE_CHECKS = 1");
    $conn->query("SET FOREIGN_KEY_CHECKS = 1");

    $max = $conn->query("SELECT MAX(idProduct) AS m FROM products")->fetch_assoc()['m'];
    $conn->query("ALTER TABLE products AUTO_INCREMENT = " . ($max + 1));
    $conn->query("ANALYZE TABLE products");

    $seedMs = round((hrtime(true) - $t0) / 1e6, 1);
    $total = $conn->query("SELECT COUNT(*) AS t FROM products")->fetch_assoc()['t'];
    return ['real' => count(REAL_PRODUCTS), 'mock' => $inserted, 'total' => (int)$total, 'seed_ms' => $seedMs];
}

// ════════════════════════════════════════════════════════════
// 3. BENCHMARK — bch() + T1-T4 (full / dbonly / php-only)
// ════════════════════════════════════════════════════════════
function bch(callable $fn, int $iters): array {
    $fn();
    gc_collect_cycles();
    $t0 = hrtime(true);
    for ($i = 0; $i < $iters; $i++) $fn();
    $sec = (hrtime(true) - $t0) / 1e9;
    return ['avg' => $sec / $iters, 'peak_kb' => round(memory_get_peak_usage(true) / 1024, 1)];
}

function t1_dbonly($conn, $kw) { $rs=$conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType WHERE p.productName LIKE '%$kw%'"); $n=0; while($rs->fetch_assoc())$n++; return $n; }
function t1_mvc($conn, $kw) { $rs=$conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType WHERE p.productName LIKE '%$kw%'"); $h=''; while($r=$rs->fetch_assoc()) $h.='<tr><td>'.htmlspecialchars($r['productName']).'</td><td>'.number_format($r['productPrice'],0,'.','.').'đ</td><td>'.htmlspecialchars($r['typeName']).'</td></tr>'; return $h; }
function t1_mvp($conn, $kw) { $rs=$conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType WHERE p.productName LIKE '%$kw%'"); $rows=[]; while($r=$rs->fetch_assoc())$rows[]=$r; $vd=array_map(fn($r)=>['name'=>htmlspecialchars($r['productName']),'price'=>number_format($r['productPrice'],0,'.','.').'đ','type'=>htmlspecialchars($r['typeName'])],$rows); $h=''; foreach($vd as $r) $h.='<tr><td>'.$r['name'].'</td><td>'.$r['price'].'</td><td>'.$r['type'].'</td></tr>'; return $h; }

function t2_dbonly($conn) { $rs=$conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType"); $n=0; while($rs->fetch_assoc())$n++; return $n; }
function t2_mvc($conn) { $rs=$conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType"); $h=''; while($r=$rs->fetch_assoc()){ $pr=(float)$r['productPrice']; $sa=(float)$r['salePrice']; $disc=$pr>0?round((1-$sa/$pr)*100):0; $h.='<tr><td>'.htmlspecialchars($r['productName']).'</td><td>'.number_format($pr,0,'.','.').'đ</td><td>'.number_format($sa,0,'.','.').'đ</td><td>'.$disc.'%</td><td>'.htmlspecialchars($r['typeName']).'</td></tr>'; } return $h; }
function t2_mvp($conn) { $rs=$conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType"); $rows=[]; while($r=$rs->fetch_assoc())$rows[]=$r; $vd=array_map(function($r){ $pr=(float)$r['productPrice']; $sa=(float)$r['salePrice']; return ['name'=>htmlspecialchars($r['productName']),'price'=>number_format($pr,0,'.','.').'đ','sale'=>number_format($sa,0,'.','.').'đ','disc'=>($pr>0?round((1-$sa/$pr)*100):0).'%','type'=>htmlspecialchars($r['typeName'])]; },$rows); $h=''; foreach($vd as $r) $h.='<tr><td>'.$r['name'].'</td><td>'.$r['price'].'</td><td>'.$r['sale'].'</td><td>'.$r['disc'].'</td><td>'.$r['type'].'</td></tr>'; return $h; }

function t3_dbonly($conn) { $rs=$conn->query("SELECT p.productPrice, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType"); $n=0; while($rs->fetch_assoc())$n++; return $n; }
function t3_mvc($conn) { $rs=$conn->query("SELECT p.productPrice, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType"); $g=[]; while($r=$rs->fetch_assoc()){ $t=$r['typeName']; if(!isset($g[$t]))$g[$t]=['total'=>0,'count'=>0,'min'=>PHP_INT_MAX,'max'=>0]; $g[$t]['total']+=$r['productPrice']; $g[$t]['count']++; if($r['productPrice']<$g[$t]['min'])$g[$t]['min']=$r['productPrice']; if($r['productPrice']>$g[$t]['max'])$g[$t]['max']=$r['productPrice']; } $h=''; foreach($g as $type=>$x){ $avg=$x['count']>0?round($x['total']/$x['count']):0; $h.='<tr><td>'.htmlspecialchars($type).'</td><td>'.$x['count'].'</td><td>'.number_format($avg,0,'.','.').'đ</td><td>'.number_format($x['min'],0,'.','.').'đ</td><td>'.number_format($x['max'],0,'.','.').'đ</td></tr>'; } return $h; }
function t3_mvp($conn) { $rs=$conn->query("SELECT p.productPrice, t.typeName FROM products p LEFT JOIN type t ON p.idType=t.idType"); $g=[]; while($r=$rs->fetch_assoc()){ $t=$r['typeName']; if(!isset($g[$t]))$g[$t]=['total'=>0,'count'=>0,'min'=>PHP_INT_MAX,'max'=>0]; $g[$t]['total']+=$r['productPrice']; $g[$t]['count']++; if($r['productPrice']<$g[$t]['min'])$g[$t]['min']=$r['productPrice']; if($r['productPrice']>$g[$t]['max'])$g[$t]['max']=$r['productPrice']; } $vd=[]; foreach($g as $type=>$x){ $avg=$x['count']>0?round($x['total']/$x['count']):0; $vd[]=['type'=>htmlspecialchars($type),'count'=>$x['count'],'avg'=>number_format($avg,0,'.','.').'đ','min'=>number_format($x['min'],0,'.','.').'đ','max'=>number_format($x['max'],0,'.','.').'đ']; } $h=''; foreach($vd as $r) $h.='<tr><td>'.$r['type'].'</td><td>'.$r['count'].'</td><td>'.$r['avg'].'</td><td>'.$r['min'].'</td><td>'.$r['max'].'</td></tr>'; return $h; }

function t4_dbonly($conn,$page,$pp) { $o=($page-1)*$pp; $rs=$conn->query("SELECT productName, productPrice FROM products ORDER BY productPrice DESC LIMIT $pp OFFSET $o"); $n=0; while($rs->fetch_assoc())$n++; return $n; }
function t4_mvc($conn,$page,$pp) { $o=($page-1)*$pp; $rs=$conn->query("SELECT productName, productPrice FROM products ORDER BY productPrice DESC LIMIT $pp OFFSET $o"); $h=''; while($r=$rs->fetch_assoc()) $h.='<tr><td>'.htmlspecialchars($r['productName']).'</td><td>'.number_format($r['productPrice'],0,'.','.').'đ</td></tr>'; return $h; }
function t4_mvp($conn,$page,$pp) { $o=($page-1)*$pp; $rs=$conn->query("SELECT productName, productPrice FROM products ORDER BY productPrice DESC LIMIT $pp OFFSET $o"); $rows=[]; while($r=$rs->fetch_assoc())$rows[]=$r; $vd=array_map(fn($r)=>['name'=>htmlspecialchars($r['productName']),'price'=>number_format($r['productPrice'],0,'.','.').'đ'],$rows); $h=''; foreach($vd as $r) $h.='<tr><td>'.$r['name'].'</td><td>'.$r['price'].'</td></tr>'; return $h; }

function run_one_scale(mysqli $conn, array $typeIds, int $mockTotal, int $iters): array {
    echo "<h3>Mức mock=$mockTotal (iters=$iters)</h3><pre style='font-family:Consolas,monospace;font-size:13px'>";
    $stats = seed_to_total($conn, $mockTotal, $typeIds);
    $total = $stats['total'];
    echo "Seed xong: {$stats['real']} sp thật + {$stats['mock']} mock = $total bản ghi ({$stats['seed_ms']} ms)\n";

    $kw = 'A0'; $page = 2; $perPage = 20;
    $tests = [
        'T1' => ['label'=>'Tìm kiếm & Lọc (LIKE %A0%)',                    'dbonly'=>fn()=>t1_dbonly($conn,$kw),             'mvc'=>fn()=>t1_mvc($conn,$kw),             'mvp'=>fn()=>t1_mvp($conn,$kw)],
        'T2' => ['label'=>'Render & Định dạng (toàn bộ bản ghi)',          'dbonly'=>fn()=>t2_dbonly($conn),                 'mvc'=>fn()=>t2_mvc($conn),                 'mvp'=>fn()=>t2_mvp($conn)],
        'T3' => ['label'=>'Phân nhóm & Thống kê (GROUP BY typeName)',      'dbonly'=>fn()=>t3_dbonly($conn),                 'mvc'=>fn()=>t3_mvc($conn),                 'mvp'=>fn()=>t3_mvp($conn)],
        'T4' => ['label'=>'Sắp xếp & Phân trang (ORDER BY + LIMIT/OFFSET)','dbonly'=>fn()=>t4_dbonly($conn,$page,$perPage),  'mvc'=>fn()=>t4_mvc($conn,$page,$perPage),  'mvp'=>fn()=>t4_mvp($conn,$page,$perPage)],
    ];

    $result = ['mock'=>$mockTotal, 'total'=>$total, 'iters'=>$iters, 'seed_ms'=>$stats['seed_ms'], 'tests'=>[]];
    foreach ($tests as $key => $t) {
        $dbonly = bch($t['dbonly'], $iters);
        $fullMvc = bch($t['mvc'], $iters);
        $fullMvp = bch($t['mvp'], $iters);
        $phpMvc = max($fullMvc['avg'] - $dbonly['avg'], 0);
        $phpMvp = max($fullMvp['avg'] - $dbonly['avg'], 0);
        $result['tests'][$key] = ['label'=>$t['label'], 'dbonly'=>$dbonly['avg'], 'full_mvc'=>$fullMvc['avg'], 'full_mvp'=>$fullMvp['avg'], 'php_mvc'=>$phpMvc, 'php_mvp'=>$phpMvp, 'mem_mvc'=>$fullMvc['peak_kb'], 'mem_mvp'=>$fullMvp['peak_kb']];
        printf("%-4s %-50s | full MVC=%9.6fs MVP=%9.6fs | DB-only=%9.6fs | PHP-only MVC=%9.6fs MVP=%9.6fs\n", $key, $t['label'], $fullMvc['avg'], $fullMvp['avg'], $dbonly['avg'], $phpMvc, $phpMvp);
    }
    echo "</pre>";
    return $result;
}

// ════════════════════════════════════════════════════════════
// 4. ĐIỀU KHIỂN — ?mock=N (1 mức) hoặc ?all=1 (cả 4 mức)
// ════════════════════════════════════════════════════════════
$typeIds = get_type_ids($conn);
echo "<h2>Kiểm tra ảnh...</h2>";
ensure_images($conn, $typeIds);

$runAll = isset($_GET['all']);
if ($runAll) {
    $scalesToRun = SCALES_DEFAULT;
} else {
    $mockTotal = isset($_GET['mock']) ? max(0, (int)$_GET['mock']) : 1000;
    $iters = isset($_GET['iters']) ? max(1, (int)$_GET['iters']) : (SCALES_DEFAULT[$mockTotal] ?? max(3, (int)round(2000 / sqrt(max($mockTotal,1)))));
    $scalesToRun = [$mockTotal => $iters];
}

$all = file_exists(RESULTS_FILE) ? (json_decode(file_get_contents(RESULTS_FILE), true) ?: []) : [];

echo "<h2>Benchmark</h2>";
foreach ($scalesToRun as $mockTotal => $iters) {
    $result = run_one_scale($conn, $typeIds, $mockTotal, $iters);
    $all[(string)$mockTotal] = $result;
    ksort($all, SORT_NUMERIC);
    file_put_contents(RESULTS_FILE, json_encode($all, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$conn->close();

$lastMock = array_key_last($scalesToRun);
$lastTotal = $all[(string)$lastMock]['total'];

echo "<hr><p>DB hiện đang giữ <b>$lastTotal bản ghi</b> (mức mock=$lastMock) — mở 
<a href='MVC/index.php'>MVC/index.php</a> hoặc <a href='MVP/index.php'>MVP/index.php</a> để xem.</p>";

echo "<p>Chạy mức khác / tất cả:</p><ul>";
foreach (SCALES_DEFAULT as $m => $it) {
    $done = isset($all[(string)$m]) ? ' ✅' : '';
    echo "<li><a href='?mock=$m'>?mock=$m</a>$done</li>";
}
echo "<li><a href='?all=1'>?all=1</a> (chạy cả 4 mức liên tiếp — sẽ lâu, nhất là mức 500000)</li>";
echo "</ul>";

echo "<p>Xem chart: <a href='chart.php'><b>chart.php</b></a></p>";
