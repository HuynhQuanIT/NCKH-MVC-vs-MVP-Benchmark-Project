<?php
include_once("controller/cType.php");

$p = new cType();
$ketqua = $p->cListType();
if (!$ketqua) {
    echo "<span style='color:#fff;'>Không có dữ liệu</span>";
} else {
    while ($r = $ketqua->fetch_assoc()) {
        echo "<a href='?idType=" . $r['idType'] . "'>" . $r['typeName'] . "</a><br>";
    }
}
?>