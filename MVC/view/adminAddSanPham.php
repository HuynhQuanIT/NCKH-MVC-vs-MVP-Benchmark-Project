<?php
include_once("controller/cType.php");

?>
<form action="#" method="post" enctype="multipart/form-data">
<h2>Thêm Sản Phẩm</h2>
Tên Sản phẩm  <input type="text" name="txtName" required placeholder="Nhập tên sản phẩm"> <br>
Giá Gốc <input type="number" name="txtPrice"required placeholder="Nhập giá gốc sản phẩm" min="0"> <br>
Giá Bán <input type="number" name ="txtSalePrice"required placeholder="Nhập giá bán sản phẩm" min="0">  <br>
Ảnh sản phẩm <input type="file" name="fileImage" id ="fileImage" > <br>
Thương Hiệu  <select name="txtType" id="txtType">
<?php
$p = new cType();
$ketqua = $p->cListType();
if ($ketqua) {
    while ($row = $ketqua->fetch_assoc()) {
        echo "<option value='
            " . $row['idType'] . "
            '>
            " . $row['typeName'] . "
            </option>";
    }
}
?>
</select> <br>
<input type="submit" name="Them" value="Thêm sản phẩm">
<input type="reset" name="NhapLai" value="Nhập lại">

</form>

 <?php
if (isset($_REQUEST["Them"])) {
    include_once("controller/cProduct.php");
    $p = new cProduct();
    $kq = $p->cInsertProduct(
        $_REQUEST["txtName"],
        $_REQUEST["txtPrice"],
        $_REQUEST["txtSalePrice"],
        $_FILES["fileImage"],
        $_REQUEST["txtType"]
    );
    if ($kq) {
        echo "<script>alert('Thêm sản phẩm thành công!')</script>";
        echo "<script>window.location.href='admin.php?sanpham';</script>";
    }
    else {
        echo "<script>alert('Thêm sản phẩm thất bại!')</script>";
    }
}
?>