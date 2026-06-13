<?php
    include_once("controller/cType.php");
    $p = new cType();
    $kq = $p->cDeleteType($_REQUEST['id']);
    if($kq){
        echo "<script>alert('Xóa thương hiệu thành công!'); window.location.href='admin.php?thuonghieu'</script>";
    }  else{
        echo"<script>alert('Xóa thương hiệu không thành công!'); window.location.href='admin.php?thuonghieu'</script>";
    }
        
?>
