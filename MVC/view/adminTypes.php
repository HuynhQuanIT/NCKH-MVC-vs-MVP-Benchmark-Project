<?php
include_once("controller/cType.php");

$p = new cType();
$rs = $p -> cListType();
if (!$rs) {
	echo "Không có dữ liệu thương hiệu";
	return;
}

echo "<table class='admin-table'>";
echo "<tr>
<th>ID</th>
<th>Tên thương hiệu</th>
<th>Thao Tác</th>
</tr>";
while ($r = $rs->fetch_assoc()) {
	echo "<tr>
			<td>".$r['idType']."</td>
			<td>".$r['typeName']."</td>
			<td class='action-cell'> <a href='?suath&id=".$r["idType"]."'>Sửa</a> | 
			<a href='?xoath&id=".$r["idType"]."' onclick='return confirm(\"Bạn có chắc chắn muốn xóa thương hiệu này không?\")'>Xóa</a> </td>
		</tr>";
}
echo "</table>";
