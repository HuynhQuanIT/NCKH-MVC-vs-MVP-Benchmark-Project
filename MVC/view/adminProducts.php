<?php
include("controller/cProduct.php");
$p=new cProduct();
if(isset($_REQUEST['idType'])){
    $rs = $p -> cListProductByType($_REQUEST['idType']);
}
elseif(isset($_REQUEST['btnTimkiem'])){

$rs = $p -> cListProductByTen($_REQUEST['ten']);
}
else{
    $rs = $p -> cListProduct();
}

if(!$rs){
    echo"khong co san pham";
}

echo "<table class='admin-table'>";
echo "<tr><th>ID</th><th>Tên sản phẩm</th><th>Giá</th><th>Giá sale</th><th>Hình</th><th>Loại</th><th class='action-cell'>Thao tác</th></tr>";
while ($r = $rs->fetch_assoc()) {

	$imgHtml = $r['image'] ? "<img src='image/".$r['image']."?v=".time()."' width='60' />" : "";
	echo "<tr>
	<td>".$r['idProduct']."</td>
	<td>".$r['productName']."</td>
	<td>".$r['productPrice']."</td>
	<td>".$r['salePrice']."</td>
	<td>".$imgHtml."</td>
	<td>".$r['typeName']."</td>
	<td class='action-cell'><a href='?suasp&id=" . $r["idProduct"] . "'>Sửa</a> | 
    <a href='?xoasp&id=" . $r["idProduct"] . "' onclick='return confirm(\"Bạn có chắc chắn muốn xóa sản phẩm này không?\")'>Xóa</a>
                </td>
	</tr>";

}
echo "</table>";	

