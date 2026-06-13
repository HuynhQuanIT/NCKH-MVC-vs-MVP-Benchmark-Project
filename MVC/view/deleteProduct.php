<?php
    include_once("controller/cProduct.php");
    $p = new cProduct();
    $kq = $p->cDeleteProduct($_REQUEST['id']);
    if($kq){
        echo "<script>alert('Xóa sản phẩm thành công!'); window.location.href='admin.php?sanpham'</script>";
    }  else{
        echo"<script>alert('Xóa sản phẩm không thành công!'); window.location.href='admin.php?sanpham'</script>";
    }
        
?>
