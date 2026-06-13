<?php
/**
 * MVP - View: vAdminAddProduct
 * Nhận $types[] (danh sách loại) từ Presenter, render form thêm sản phẩm.
 * Không gọi bất kỳ Presenter/Model nào.
 */
?>
<form action="#" method="post" enctype="multipart/form-data">
    <h2>Thêm Sản Phẩm</h2>
    Tên Sản phẩm  <input type="text"   name="txtName"      required placeholder="Nhập tên sản phẩm"><br>
    Giá Gốc       <input type="number" name="txtPrice"      required placeholder="Nhập giá gốc" min="0"><br>
    Giá Bán       <input type="number" name="txtSalePrice"  required placeholder="Nhập giá bán" min="0"><br>
    Ảnh sản phẩm  <input type="file"   name="fileImage"><br>
    Thương Hiệu
    <select name="txtType">
        <?php foreach ($types as $t): ?>
            <option value="<?= (int)$t['idType'] ?>"><?= htmlspecialchars($t['typeName']) ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="submit" name="Them" value="Thêm sản phẩm">
    <input type="reset"  name="NhapLai" value="Nhập lại">
</form>
