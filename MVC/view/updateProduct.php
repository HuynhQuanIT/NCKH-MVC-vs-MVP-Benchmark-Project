<?php

include_once("controller/cProduct.php");

$p = new cProduct();
$ketqua = $p->cListProductById($_REQUEST['id']);
if ($ketqua) {
    while ($row = $ketqua->fetch_assoc()) {
        $name = $row['productName'];
        $price = $row['productPrice'];
        $salePrice = $row['salePrice'];
        $image = $row['image'];
        $type = $row['idType'];
    }
}
else {
    echo "<script>alert('Mã sản phẩm không tồn tại')</script>";
    echo "<script>window.location.href='admin.php';</script>";
}
?>

<form action="" method="POST" enctype="multipart/form-data">
<h2>Cập nhật sản phẩm</h2>
Tên Sản phẩm  <input type="text" name="txtName" value="<?php if (isset($name))
    echo $name; ?>" required placeholder="Nhập tên sản phẩm" > <br>
Giá Gốc <input type="number" name="txtPrice" value="<?php if (isset($price))
    echo $price; ?>" required placeholder="Nhập giá gốc" min="0"> <br>
Giá Bán <input type="number" name ="txtSalePrice"value="<?php if (isset($salePrice))
    echo $salePrice; ?>" required placeholder="Nhập giá bán" min="0">  <br>
Ảnh sản phẩm <input type="file" name="fileImage"> <br>
<img width='150' height='150' src="image/<?php if (isset($image))
    echo $image; ?>?v=<?=time()?>"> <br>
Thương Hiệu  <select name="txtType" id="txtType"value="<?php if (isset($type))
    echo $type; ?>">
<?php
include_once("controller/cType.php");
$p = new cType();
$ketqua = $p->cListType();
if ($ketqua) {
    while ($row = $ketqua->fetch_assoc()) {
        if ($row["idType"] == $type) {
            echo "<option value='" . $row["idType"] . "' selected>" . $row["typeName"] . "</option>";
        }
        else {
            echo "<option value='
                        " . $row['idType'] . "
                        '>
                        " . $row['typeName'] . "
                        </option>";
        }
    }
}
?>
</select> <br>
<input type="submit" name="Sua" value="Cập nhật sản phẩm">
<input type="reset" name="NhapLai" value="Nhập lại">

</form>
<?php
if (isset($_REQUEST['Sua'])) {
    $p = new cProduct();
    $ketqua = $p->cUpdateProduct(
        $_REQUEST['id'],
        $_REQUEST['txtName'],
        $_REQUEST['txtPrice'],
        $_REQUEST['txtSalePrice'],
        $_FILES['fileImage'],
        $image,
        $_REQUEST['txtType']
    );
    if ($ketqua) {
        echo "<script>alert('Cập nhật sản phẩm thành công!'); window.location.href='admin.php?sanpham' </script>";
    }
    else {
        echo "<script>alert('Cập nhật sản phẩm không thành công!'); window.location.href='admin.php?suasp' </script>";
    }

}
?>
