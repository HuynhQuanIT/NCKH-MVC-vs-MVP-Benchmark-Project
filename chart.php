<?php
/**
 * ============================================================
 *  chart.php — chỉ ĐỌC kết quả đã tích luỹ, KHÔNG đụng DB
 * ============================================================
 *  Đọc results_scalability.json (do benchmark.php ghi ra mỗi lần
 *  chạy 1 mức), vẽ 3 nhóm biểu đồ:
 *
 *   1. Tổng thời gian (full = query + transform + render)
 *   2. PHP-only time (full - DB-only) — nơi MVC vs MVP khác biệt thật
 *   3. % chênh lệch PHP-only MVP so với MVC theo từng mức
 *
 *  Trục X dùng SỐ SẢN PHẨM MOCK (1.000 / 5.000 / 50.000 / 500.000) —
 *  đây cũng chính là tổng số bản ghi trong bảng products, vì benchmark.php
 *  đã bỏ hoàn toàn 12 sản phẩm thật cố định, chỉ còn data mock thuần.
 *
 *  MVC = đường liền, chấm tròn, màu xanh dương (#2f6fb3)
 *  MVP = đường đứt,  chấm vuông, màu xanh lá  (#2a9d6f)
 * ============================================================
 */

const RESULTS_FILE = __DIR__ . '/results_scalability.json';
const COLOR_MVC = '#2f6fb3';
const COLOR_MVP = '#e0762a'; // đổi sang màu cam để tách bạch rõ với xanh lá của lưới/nền

if (!file_exists(RESULTS_FILE)) {
    die("Chưa có dữ liệu. Hãy chạy <a href='benchmark.php?mock=1000'>benchmark.php?mock=1000</a> (và các mức 5000/50000/500000) trước.");
}

$all = json_decode(file_get_contents(RESULTS_FILE), true) ?: [];
if (empty($all)) {
    die("results_scalability.json rỗng. Hãy chạy benchmark.php với từng mức ?mock= trước.");
}

ksort($all, SORT_NUMERIC);
$mocks = array_keys($all); // string keys, đã sort numeric theo giá trị mock

$first = reset($all);
$testKeys = array_keys($first['tests']);
$labels = [];
foreach ($testKeys as $tk) $labels[$tk] = $first['tests'][$tk]['label'];

// Trục X = số mock THUẦN (không +12) -> 1.000 / 5.000 / 50.000 / 500.000
$totalsX = array_map(fn($m) => (int)$m, $mocks);

// ════════════════════════════════════════════════════════════
// SVG LINE CHART (log-x, log-y) — MVC liền/tròn vs MVP đứt/vuông
// ════════════════════════════════════════════════════════════
function render_trend_chart(string $title, array $totalsX, array $mvcVals, array $mvpVals, string $mvcColor = COLOR_MVC, string $mvpColor = COLOR_MVP): string {
    $W = 580; $H = 340;
    $padL = 68; $padR = 24; $padT = 40; $padB = 54;
    $plotW = $W - $padL - $padR; $plotH = $H - $padT - $padB;

    $logXs = array_map('log10', $totalsX);
    $minLogX = min($logXs); $maxLogX = max($logXs);
    if ($minLogX === $maxLogX) { $minLogX -= 0.5; $maxLogX += 0.5; }

    $safe = fn($v) => max($v, 1e-9);
    $allVals = array_merge(array_map($safe, $mvcVals), array_map($safe, $mvpVals));
    $logYs = array_map('log10', $allVals);
    $minLogY = min($logYs); $maxLogY = max($logYs);
    if ($minLogY === $maxLogY) { $minLogY -= 0.5; $maxLogY += 0.5; }
    // chừa lề trên/dưới 8% để điểm không dính sát viền
    $rangeY = $maxLogY - $minLogY; $minLogY -= $rangeY * 0.08; $maxLogY += $rangeY * 0.08;

    $xPos = fn($logx) => $padL + ($logx - $minLogX) / ($maxLogX - $minLogX) * $plotW;
    $yPos = fn($logy) => $padT + (1 - ($logy - $minLogY) / ($maxLogY - $minLogY)) * $plotH;

    $mvcPts = []; $mvpPts = []; $markers = '';
    foreach ($totalsX as $i => $x) {
        $px = $xPos($logXs[$i]);
        $pyMvc = $yPos(log10($safe($mvcVals[$i])));
        $pyMvp = $yPos(log10($safe($mvpVals[$i])));
        $mvcPts[] = "$px,$pyMvc"; $mvpPts[] = "$px,$pyMvp";

        // MVC: chấm tròn
        $markers .= "<circle cx='$px' cy='$pyMvc' r='5' fill='".COLOR_MVC."' stroke='#fff' stroke-width='1.5'/>";
        // MVP: chấm vuông (xoay 45° nhìn như diamond để khác hẳn hình tròn)
        $markers .= "<rect x='".($px-4.5)."' y='".($pyMvp-4.5)."' width='9' height='9' fill='".COLOR_MVP."' stroke='#fff' stroke-width='1.5' transform='rotate(45 $px $pyMvp)'/>";

        $lbl = number_format($x, 0, '.', '.');
        $markers .= "<text x='$px' y='".($H-$padB+20)."' font-size='11' text-anchor='middle' fill='#555'>$lbl</text>";
    }

    $gridLines = '';
    for ($g = 0; $g <= 4; $g++) {
        $logy = $minLogY + ($maxLogY - $minLogY) * $g / 4;
        $y = $yPos($logy);
        $val = pow(10, $logy);
        $valLbl = $val >= 1 ? number_format($val, 2) . 's' : number_format($val * 1000, 1) . 'ms';
        $gridLines .= "<line x1='$padL' y1='$y' x2='".($W-$padR)."' y2='$y' stroke='#ebebeb'/>";
        $gridLines .= "<text x='".($padL-10)."' y='".($y+3)."' font-size='10' text-anchor='end' fill='#777'>$valLbl</text>";
    }

    $mvcLine = implode(' ', $mvcPts);
    $mvpLine = implode(' ', $mvpPts);

    // Tính trước toạ độ legend (KHÔNG ghép trực tiếp "$W-178" trong chuỗi vì
    // PHP sẽ nội suy $W rồi nối chuỗi "-178" thay vì tính toán trừ)
    $bottomLabelY = $H - 10;
    $legendY1 = 22; $legendY2 = 38;
    $legLineX1 = $W - 178; $legLineX2 = $W - 150;
    $legDotX   = $W - 164; $legTextX  = $W - 144;
    $legendY1Text = $legendY1 + 4; $legendY2Text = $legendY2 + 4;
    $legRectY2 = $legendY2 - 4;

    return <<<SVG
<svg width="100%" viewBox="0 0 $W $H" xmlns="http://www.w3.org/2000/svg" style="background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
  <text x="$padL" y="20" font-size="14" font-weight="bold" fill="#222">$title</text>
  $gridLines
  <polyline points="$mvcLine" fill="none" stroke="$mvcColor" stroke-width="2.5"/>
  <polyline points="$mvpLine" fill="none" stroke="$mvpColor" stroke-width="2.5" stroke-dasharray="7,5"/>
  $markers
  <text x="$padL" y="$bottomLabelY" font-size="10" fill="#888">Số bản ghi mock (trục log)</text>

  <line x1="$legLineX1" y1="$legendY1" x2="$legLineX2" y2="$legendY1" stroke="$mvcColor" stroke-width="2.5"/>
  <circle cx="$legDotX" cy="$legendY1" r="4" fill="$mvcColor"/>
  <text x="$legTextX" y="$legendY1Text" font-size="11" fill="#333">MVC (liền — ●)</text>

  <line x1="$legLineX1" y1="$legendY2" x2="$legLineX2" y2="$legendY2" stroke="$mvpColor" stroke-width="2.5" stroke-dasharray="7,5"/>
  <rect x="$legDotX" y="$legRectY2" width="8" height="8" fill="$mvpColor" transform="rotate(45 $legDotX $legendY2)"/>
  <text x="$legTextX" y="$legendY2Text" font-size="11" fill="#333">MVP (đứt — ◆)</text>
</svg>
SVG;
}

// ════════════════════════════════════════════════════════════
// SVG BAR CHART — % chênh lệch (MVP-MVC)/MVC theo từng mức (PHP-only)
// ════════════════════════════════════════════════════════════
function render_diff_bar_chart(string $title, array $totalsX, array $diffPct, string $mvcColor = COLOR_MVC, string $mvpColor = COLOR_MVP): string {
    $W = 580; $H = 280;
    $padL = 60; $padR = 24; $padT = 40; $padB = 46;
    $plotW = $W - $padL - $padR; $plotH = $H - $padT - $padB;

    $maxAbs = max(1, max(array_map('abs', $diffPct)));
    $zeroY = $padT + $plotH / 2;
    $scaleY = ($plotH / 2) / $maxAbs;

    $n = count($totalsX);
    $slot = $plotW / $n;
    $barW = $slot * 0.45;

    $bars = ''; $xlabels = '';
    foreach ($totalsX as $i => $x) {
        $cx = $padL + $slot * $i + $slot / 2;
        $d = $diffPct[$i];
        $h = abs($d) * $scaleY;
        $y = $d >= 0 ? $zeroY - $h : $zeroY;
        $color = $d > 1 ? COLOR_MVC : ($d < -1 ? COLOR_MVP : '#bbbbbb');
        $bx = $cx - $barW / 2;
        $bars .= "<rect x='$bx' y='$y' width='$barW' height='" . max($h, 1) . "' rx='3' fill='$color'/>";
        $valLbl = ($d >= 0 ? '+' : '') . number_format($d, 1) . '%';
        $textY = $d >= 0 ? $y - 6 : $y + $h + 14;
        $bars .= "<text x='$cx' y='$textY' font-size='11' text-anchor='middle' fill='#333'>$valLbl</text>";
        $xlabels .= "<text x='$cx' y='" . ($H - $padB + 20) . "' font-size='11' text-anchor='middle' fill='#555'>" . number_format($x, 0, '.', '.') . "</text>";
    }

    return <<<SVG
<svg width="100%" viewBox="0 0 $W $H" xmlns="http://www.w3.org/2000/svg" style="background:#fff;border:1px solid #ddd;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,0.06)">
  <text x="$padL" y="20" font-size="14" font-weight="bold" fill="#222">$title</text>
  <line x1="$padL" y1="$zeroY" x2="$W-$padR" y2="$zeroY" stroke="#999"/>
  $bars
  $xlabels
  <text x="$padL" y="$H-8" font-size="10" fill="#888">Số bản ghi mock</text>
  <rect x="$W-210" y="14" width="10" height="10" rx="2" fill="$mvcColor"/>
  <text x="$W-196" y="23" font-size="11" fill="#333">MVC nhanh hơn</text>
  <rect x="$W-100" y="14" width="10" height="10" rx="2" fill="$mvpColor"/>
  <text x="$W-86" y="23" font-size="11" fill="#333">MVP nhanh hơn</text>
</svg>
SVG;
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Chart — MVC vs MVP Scalability</title>
<style>
body { font-family: "Times New Roman", serif; font-size: 11pt; max-width: 1020px; margin: 30px auto; }
h1 { font-size: 14pt; text-align: center; }
h2 { font-size: 12.5pt; margin-top: 36px; border-bottom: 2px solid #2f6fb3; padding-bottom: 4px; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 9.5pt; }
th, td { border: 1px solid #999; padding: 5px 8px; text-align: center; }
th { background: #d0dff0; }
td.left { text-align: left; }
.win-mvc { background: #cce0ff; font-weight: bold; }
.win-mvp { background: #ffe6cf; font-weight: bold; }
.win-tie { background: #f0f0f0; }
.charts { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; margin-top: 16px; }
@media (max-width: 700px) { .charts { grid-template-columns: 1fr; } }
.note { font-size: 9pt; color: #555; }
.status { font-size: 9pt; background:#f7f7f7; border:1px solid #ddd; padding:6px 10px; margin-bottom:14px; }
</style>
</head>
<body>
<h1>Benchmark MVC vs MVP — Scalability<br>
<small style="font-size:10pt">1.000 → 500.000 sản phẩm mock, đơn vị: giây (s)</small></h1>

<div class="status">
Các mức đã có dữ liệu: <b><?= implode(', ', array_map(fn($x) => number_format($x, 0, '.', '.'), $totalsX)) ?></b> sản phẩm.
Muốn thêm/làm lại 1 mức: chạy <code>benchmark.php?mock=N</code>.
</div>

<h2>1. Bảng chi tiết theo từng mức (full / DB-only / PHP-only)</h2>
<?php foreach ($mocks as $m): $entry = $all[$m]; ?>
<h3 style="font-size:11pt;margin-top:18px">Mức <?= number_format((int)$m,0,'.','.') ?> sản phẩm (<?= $entry['iters'] ?> vòng lặp)</h3>
<table>
<tr><th class="left">Bài toán</th><th>Full MVC (s)</th><th>Full MVP (s)</th><th>DB-only (s)</th><th>PHP-only MVC (s)</th><th>PHP-only MVP (s)</th><th>% chênh PHP-only</th></tr>
<?php foreach ($entry['tests'] as $key => $t):
    $phpMvc = $t['php_mvc']; $phpMvp = $t['php_mvp'];
    $diff = $phpMvc > 0 ? round(($phpMvp - $phpMvc) / $phpMvc * 100, 1) : 0;
    $wMvc = $diff > 1 ? 'win-mvc' : ($diff < -1 ? '' : 'win-tie');
    $wMvp = $diff < -1 ? 'win-mvp' : ($diff > 1 ? '' : 'win-tie');
?>
<tr>
    <td class="left"><b><?= $key ?></b> — <?= $t['label'] ?></td>
    <td><?= number_format($t['full_mvc'],5) ?></td>
    <td><?= number_format($t['full_mvp'],5) ?></td>
    <td><?= number_format($t['dbonly'],5) ?></td>
    <td class="<?= $wMvc ?>"><?= number_format($phpMvc,6) ?></td>
    <td class="<?= $wMvp ?>"><?= number_format($phpMvp,6) ?></td>
    <td><?= ($diff>=0?'+':'').$diff ?>%</td>
</tr>
<?php endforeach; ?>
</table>
<?php endforeach; ?>


<h2>2. PHP-only time (đã loại DB/network) — nơi MVC vs MVP khác biệt thật</h2>
<p class="note">php_only = full − db_only. Ở mức 500.000, số vòng lặp ít hơn (5–8 lần) nên dễ bị nhiễu — nếu thấy đường gãy bất thường ở điểm cuối, đó là nhiễu đo, không phải xu hướng thật; nên chạy lại <code>benchmark.php?mock=500000&iters=20</code> vài lần để xác nhận.</p>
<div class="charts">
<?php foreach ($testKeys as $tk):
    $mvcVals = array_map(fn($m) => $all[$m]['tests'][$tk]['php_mvc'], $mocks);
    $mvpVals = array_map(fn($m) => $all[$m]['tests'][$tk]['php_mvp'], $mocks);
    echo render_trend_chart("$tk — {$labels[$tk]} (PHP-only)", $totalsX, $mvcVals, $mvpVals);
endforeach; ?>
</div>

<p class="note" style="margin-top:24px">
    Để cập nhật/thêm mức mới: chạy <a href="benchmark.php">benchmark.php?mock=N</a> rồi quay lại trang này.
</p>

</body>
</html>