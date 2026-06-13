<?php 
include_once("controller/cType.php");

$p = new cType();
$ketqua = $p->cListTypeById($_REQUEST['id']);
if($ketqua){
    while($row = $ketqua->fetch_assoc()){
        $name = $row['typeName'];
    }
}else {
        echo "<script>alert('Mã Thương hiệu không tồn tại')</script>";
        echo "<script>window.location.href='admin.php';</script>";
    }
?>

<form action="" method="POST" enctype="multipart/form-data">
<h2>Cập nhật Thương Hiệu</h2>
Tên Thương Hiệu  <input type="text" name="txtName" value="<?php if (isset($name)) echo $name; ?>" > <br>
</select> <br>
<input type="submit" name="Sua" value="Cập nhật Thương Hiệu">
<input type="reset" name="NhapLai" value="Nhập lại">

</form>
<?php
    if(isset($_REQUEST['Sua'])){
        $ketqua = $p -> cUpdateType(
            $_REQUEST['id'],
            $_REQUEST['txtName'],

        );
    if($ketqua){
        echo"<script>alert('Cập nhật thương hiệu thành công!'); window.location.href='admin.php?thuonghieu' </script>";
    }else{
        echo"<script>alert('Cập nhật thương hiệu không thành công!'); window.location.href='admin.php?suath' </script>";
    }

    }
?>
