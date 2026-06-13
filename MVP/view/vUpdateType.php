<?php
/**
 * MVP - View: vUpdateType
 * Nhận $typeData (array) từ Presenter, render form cập nhật thương hiệu.
 */
?>
<form action="" method="POST" enctype="multipart/form-data">
    <h2>Cập nhật Thương Hiệu</h2>
    Tên Thương Hiệu <input type="text" name="txtName" value="<?= htmlspecialchars($typeData['typeName']) ?>"><br>
    <input type="submit" name="Sua"    value="Cập nhật Thương Hiệu">
    <input type="reset"  name="NhapLai" value="Nhập lại">
</form>
