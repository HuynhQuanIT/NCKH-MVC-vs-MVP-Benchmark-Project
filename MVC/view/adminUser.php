<?php
include_once("controller/cUser.php");

// price styles

$p=new cUser();
$rs = $p -> cListUser();
if (!$rs) {
	echo "Không có dữ liệu người dùng";
	return;
}

echo "<table class='admin-table'>";
echo "<tr>
<th>Mã người dùng</th>
<th>Tên Người dùng</th>
<th>Vai trò</th>
</tr>";
while ($r = $rs->fetch_assoc()) {
	echo "<tr><td>".$r['id']."</td><td>".$r['username'] ."</td><td>".$r['nameRole']."</td></tr>";
}
echo "</table>";



