<?php
/**
 * ============================================================
 *  BENCHMARK TRÊN DATABASE THẬT: MVC vs MVP
 *  Chạy 4 bài toán trên dữ liệu thực (5.000 bản ghi từ seed_5000.php)
 * ============================================================
 *  Cách dùng:
 *  1. Chạy seed_5000.php trước để có 5.000 bản ghi
 *  2. Đặt file này cùng cấp với MVC/, MVP/
 *  3. Truy cập: http://localhost/NCKH/benchmark_real_db.php
 * ============================================================
 */

ini_set('max_execution_time', 180);
define('ITERS', 100); // ít hơn mock vì có DB I/O thật (mỗi lần query DB)

$conn = mysqli_connect("localhost", "root", "", "qlbh");
if (!$conn) die("Lỗi kết nối: " . mysqli_connect_error());
mysqli_set_charset($conn, "utf8mb4");

$check = $conn->query("SELECT COUNT(*) AS total FROM products");
$total = $check->fetch_assoc()['total'];
if ($total < 1000) {
    die("⚠ Bảng products chỉ có $total bản ghi. Hãy chạy seed_5000.php trước!");
}

// ── Helper: bench với hrtime + gc + memory ───────────────────
function bch(callable $fn, int $iters): array {
    $fn(); // warm-up
    gc_collect_cycles();
    $t0 = hrtime(true);
    for ($i = 0; $i < $iters; $i++) $fn();
    $ms = (hrtime(true) - $t0) / 1e6;
    return [
        'avg'     => round($ms * 1000 / $iters, 3), // µs
        'total'   => round($ms, 2),                  // ms
        'peak_kb' => round(memory_get_peak_usage(true) / 1024, 1),
    ];
}

// ════════════════════════════════════════════════════════════
// T1 — TÌM KIẾM & LỌC
// ════════════════════════════════════════════════════════════
$kw = 'A0';

// MVC: View tự query + lọc + render trong 1 vòng lặp
function t1_mvc($conn, $kw) {
    $rs = $conn->query("SELECT p.*, t.typeName FROM products p
                         LEFT JOIN type t ON p.idType = t.idType
                         WHERE p.productName LIKE '%$kw%'");
    $h = '';
    while ($r = $rs->fetch_assoc()) {
        $h .= '<tr><td>'.htmlspecialchars($r['productName']).'</td>'
            . '<td>'.number_format($r['productPrice'],0,'.','.').'đ</td>'
            . '<td>'.htmlspecialchars($r['typeName']).'</td></tr>';
    }
    return $h;
}

// MVP: Presenter query + transform thành array -> View render
function t1_mvp($conn, $kw) {
    $rs = $conn->query("SELECT p.*, t.typeName FROM products p
                         LEFT JOIN type t ON p.idType = t.idType
                         WHERE p.productName LIKE '%$kw%'");
    $rows = [];
    while ($r = $rs->fetch_assoc()) $rows[] = $r;

    $viewData = array_map(fn($r) => [
        'name'  => htmlspecialchars($r['productName']),
        'price' => number_format($r['productPrice'],0,'.','.') . 'đ',
        'type'  => htmlspecialchars($r['typeName']),
    ], $rows);

    $h = '';
    foreach ($viewData as $r) {
        $h .= '<tr><td>'.$r['name'].'</td><td>'.$r['price'].'</td><td>'.$r['type'].'</td></tr>';
    }
    return $h;
}

// ════════════════════════════════════════════════════════════
// T2 — RENDER & ĐỊNH DẠNG (toàn bộ 5.000 dòng)
// ════════════════════════════════════════════════════════════
function t2_mvc($conn) {
    $rs = $conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType");
    $h = '';
    while ($r = $rs->fetch_assoc()) {
        $pr = (float)$r['productPrice']; $sa = (float)$r['salePrice'];
        $disc = $pr > 0 ? round((1 - $sa/$pr) * 100) : 0;
        $h .= '<tr><td>'.htmlspecialchars($r['productName']).'</td>'
            . '<td>'.number_format($pr,0,'.','.').'đ</td>'
            . '<td>'.number_format($sa,0,'.','.').'đ</td>'
            . '<td>'.$disc.'%</td>'
            . '<td>'.htmlspecialchars($r['typeName']).'</td></tr>';
    }
    return $h;
}

function t2_mvp($conn) {
    $rs = $conn->query("SELECT p.*, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType");
    $rows = [];
    while ($r = $rs->fetch_assoc()) $rows[] = $r;

    $viewData = array_map(function($r) {
        $pr = (float)$r['productPrice']; $sa = (float)$r['salePrice'];
        return [
            'name'  => htmlspecialchars($r['productName']),
            'price' => number_format($pr,0,'.','.') . 'đ',
            'sale'  => number_format($sa,0,'.','.') . 'đ',
            'disc'  => ($pr > 0 ? round((1 - $sa/$pr) * 100) : 0) . '%',
            'type'  => htmlspecialchars($r['typeName']),
        ];
    }, $rows);

    $h = '';
    foreach ($viewData as $r) {
        $h .= '<tr><td>'.$r['name'].'</td><td>'.$r['price'].'</td><td>'.$r['sale'].'</td><td>'.$r['disc'].'</td><td>'.$r['type'].'</td></tr>';
    }
    return $h;
}

// ════════════════════════════════════════════════════════════
// T3 — PHÂN NHÓM & THỐNG KÊ (theo typeName)
// ════════════════════════════════════════════════════════════
function t3_mvc($conn) {
    $rs = $conn->query("SELECT p.productPrice, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType");
    $g = [];
    while ($r = $rs->fetch_assoc()) {
        $t = $r['typeName'];
        if (!isset($g[$t])) $g[$t] = ['total'=>0,'count'=>0,'min'=>PHP_INT_MAX,'max'=>0];
        $g[$t]['total'] += $r['productPrice'];
        $g[$t]['count']++;
        if ($r['productPrice'] < $g[$t]['min']) $g[$t]['min'] = $r['productPrice'];
        if ($r['productPrice'] > $g[$t]['max']) $g[$t]['max'] = $r['productPrice'];
    }
    $h = '';
    foreach ($g as $type => $x) {
        $avg = $x['count'] > 0 ? round($x['total']/$x['count']) : 0;
        $h .= '<tr><td>'.htmlspecialchars($type).'</td><td>'.$x['count'].'</td>'
            . '<td>'.number_format($avg,0,'.','.').'đ</td>'
            . '<td>'.number_format($x['min'],0,'.','.').'đ</td>'
            . '<td>'.number_format($x['max'],0,'.','.').'đ</td></tr>';
    }
    return $h;
}

function t3_mvp($conn) {
    $rs = $conn->query("SELECT p.productPrice, t.typeName FROM products p LEFT JOIN type t ON p.idType = t.idType");
    $g = [];
    while ($r = $rs->fetch_assoc()) {
        $t = $r['typeName'];
        if (!isset($g[$t])) $g[$t] = ['total'=>0,'count'=>0,'min'=>PHP_INT_MAX,'max'=>0];
        $g[$t]['total'] += $r['productPrice'];
        $g[$t]['count']++;
        if ($r['productPrice'] < $g[$t]['min']) $g[$t]['min'] = $r['productPrice'];
        if ($r['productPrice'] > $g[$t]['max']) $g[$t]['max'] = $r['productPrice'];
    }
    $viewData = [];
    foreach ($g as $type => $x) {
        $avg = $x['count'] > 0 ? round($x['total']/$x['count']) : 0;
        $viewData[] = [
            'type'  => htmlspecialchars($type),
            'count' => $x['count'],
            'avg'   => number_format($avg,0,'.','.') . 'đ',
            'min'   => number_format($x['min'],0,'.','.') . 'đ',
            'max'   => number_format($x['max'],0,'.','.') . 'đ',
        ];
    }
    $h = '';
    foreach ($viewData as $r) {
        $h .= '<tr><td>'.$r['type'].'</td><td>'.$r['count'].'</td><td>'.$r['avg'].'</td><td>'.$r['min'].'</td><td>'.$r['max'].'</td></tr>';
    }
    return $h;
}

// ════════════════════════════════════════════════════════════
// T4 — SẮP XẾP & PHÂN TRANG (trang 2, 20 sp/trang)
// ════════════════════════════════════════════════════════════
$page = 2; $perPage = 20;

// MVC: ORDER BY + LIMIT/OFFSET trực tiếp trong SQL, View render
function t4_mvc($conn, $page, $perPage) {
    $offset = ($page - 1) * $perPage;
    $rs = $conn->query("SELECT productName, productPrice FROM products
                         ORDER BY productPrice DESC LIMIT $perPage OFFSET $offset");
    $h = '';
    while ($r = $rs->fetch_assoc()) {
        $h .= '<tr><td>'.htmlspecialchars($r['productName']).'</td>'
            . '<td>'.number_format($r['productPrice'],0,'.','.').'đ</td></tr>';
    }
    return $h;
}

// MVP: Presenter query + format -> View render
function t4_mvp($conn, $page, $perPage) {
    $offset = ($page - 1) * $perPage;
    $rs = $conn->query("SELECT productName, productPrice FROM products
                         ORDER BY productPrice DESC LIMIT $perPage OFFSET $offset");
    $rows = [];
    while ($r = $rs->fetch_assoc()) $rows[] = $r;

    $viewData = array_map(fn($r) => [
        'name'  => htmlspecialchars($r['productName']),
        'price' => number_format($r['productPrice'],0,'.','.') . 'đ',
    ], $rows);

    $h = '';
    foreach ($viewData as $r) {
        $h .= '<tr><td>'.$r['name'].'</td><td>'.$r['price'].'</td></tr>';
    }
    return $h;
}

// ════════════════════════════════════════════════════════════
// CHẠY BENCHMARK
// ════════════════════════════════════════════════════════════
$results = [];
$results['T1'] = ['mvc' => bch(fn() => t1_mvc($conn, $kw), ITERS), 'mvp' => bch(fn() => t1_mvp($conn, $kw), ITERS)];
$results['T2'] = ['mvc' => bch(fn() => t2_mvc($conn),      ITERS), 'mvp' => bch(fn() => t2_mvp($conn),      ITERS)];
$results['T3'] = ['mvc' => bch(fn() => t3_mvc($conn),      ITERS), 'mvp' => bch(fn() => t3_mvp($conn),      ITERS)];
$results['T4'] = ['mvc' => bch(fn() => t4_mvc($conn, $page, $perPage), ITERS), 'mvp' => bch(fn() => t4_mvp($conn, $page, $perPage), ITERS)];

$conn->close();

// ════════════════════════════════════════════════════════════
// HIỂN THỊ KẾT QUẢ
// ════════════════════════════════════════════════════════════
$labels = [
    'T1' => 'Tìm kiếm & Lọc (LIKE %A0%)',
    'T2' => 'Render & Định dạng (toàn bộ 5.000 dòng)',
    'T3' => 'Phân nhóm & Thống kê (GROUP BY typeName)',
    'T4' => 'Sắp xếp & Phân trang (ORDER BY + LIMIT/OFFSET)',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Benchmark MVC vs MVP — DB thật (5.000 bản ghi)</title>
<style>
body { font-family: "Times New Roman", serif; font-size: 11pt; max-width: 900px; margin: 30px auto; }
h1 { font-size: 14pt; text-align: center; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 10pt; }
th, td { border: 1px solid #999; padding: 6px 10px; text-align: center; }
th { background: #d0dff0; }
td.left { text-align: left; }
.win-mvc { background: #cce0ff; font-weight: bold; }
.win-mvp { background: #c8f0e0; font-weight: bold; }
.win-tie { background: #f0f0f0; }
.note { font-size: 9pt; color: #555; }
</style>
</head>
<body>
<h1>Kết quả Benchmark MVC vs MVP trên Database thật<br>(<?= $total ?> bản ghi, <?= ITERS ?> vòng lặp/bài toán)</h1>

<table>
<tr><th style="text-align:left">Bài toán</th><th>MVC (µs)</th><th>MVP (µs)</th><th>Chênh lệch</th><th>Peak Mem MVC</th><th>Peak Mem MVP</th></tr>
<?php foreach ($results as $key => $r):
    $mvc = $r['mvc']['avg']; $mvp = $r['mvp']['avg'];
    $diff = round(($mvp - $mvc) / $mvc * 100, 1);
    $winMvc = $diff > 1 ? 'win-mvc' : '';
    $winMvp = $diff < -1 ? 'win-mvp' : '';
    if (abs($diff) <= 1) { $winMvc = $winMvp = 'win-tie'; }
?>
<tr>
    <td class="left"><b><?= $key ?></b> — <?= $labels[$key] ?></td>
    <td class="<?= $winMvc ?>"><?= number_format($mvc,2) ?></td>
    <td class="<?= $winMvp ?>"><?= number_format($mvp,2) ?></td>
    <td><?= ($diff>=0?'+':'').$diff ?>%</td>
    <td><?= $r['mvc']['peak_kb'] ?> KB</td>
    <td><?= $r['mvp']['peak_kb'] ?> KB</td>
</tr>
<?php endforeach; ?>
</table>

<p class="note">
    Đơn vị: µs/request (microsecond), bao gồm cả thời gian query MySQL thực tế.<br>
    Chênh lệch dương = MVC nhanh hơn MVP; âm = MVP nhanh hơn MVC.<br>
    Đo bằng <code>hrtime(true)</code> + <code>gc_collect_cycles()</code> + <code>memory_get_peak_usage(true)</code>,
    <?= ITERS ?> vòng lặp (ít hơn benchmark mock vì có chi phí I/O thật từ MySQL).
</p>

<h3>So với kết quả benchmark mock (không DB):</h3>
<table>
<tr><th style="text-align:left">Bài toán</th><th>Mock (không DB)</th><th>DB thật (<?= $total ?> dòng)</th><th>Nhận xét</th></tr>
<tr><td class="left">T1 Tìm kiếm</td><td>+34,2% (MVC nhanh)</td>
    <td><?= round(($results['T1']['mvp']['avg']-$results['T1']['mvc']['avg'])/$results['T1']['mvc']['avg']*100,1) ?>%</td>
    <td class="left">Chi phí query MySQL chiếm phần lớn → chênh lệch kiến trúc bị "pha loãng"</td></tr>
<tr><td class="left">T2 Render</td><td>+21,5% (MVC nhanh)</td>
    <td><?= round(($results['T2']['mvp']['avg']-$results['T2']['mvc']['avg'])/$results['T2']['mvc']['avg']*100,1) ?>%</td>
    <td class="left">Tương tự — I/O dominates</td></tr>
<tr><td class="left">T3 Thống kê</td><td>−1,1% (MVP nhanh)</td>
    <td><?= round(($results['T3']['mvp']['avg']-$results['T3']['mvc']['avg'])/$results['T3']['mvc']['avg']*100,1) ?>%</td>
    <td class="left">Output nhỏ (vài nhóm) — xu hướng giữ nguyên</td></tr>
<tr><td class="left">T4 Phân trang</td><td>+0,5% (tương đương)</td>
    <td><?= round(($results['T4']['mvp']['avg']-$results['T4']['mvc']['avg'])/$results['T4']['mvc']['avg']*100,1) ?>%</td>
    <td class="left">LIMIT/OFFSET ở SQL → cả 2 đều rất nhanh, chênh lệch nhỏ</td></tr>
</table>

</body>
</html>
