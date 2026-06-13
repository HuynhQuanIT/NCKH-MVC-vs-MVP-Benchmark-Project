<?php
/**
 * MVP - View: vUpdateProduct
 * Nhận $product (array) và $types[] từ Presenter.
 * Không có logic - chỉ render form cập nhật sản phẩm.
 */
?>
<form action="" method="POST" enctype="multipart/form-data">
    <h2>Cập nhật sản phẩm</h2>
    Tên Sản phẩm <input type="text"   name="txtName"     value="<?= htmlspecialchars($product['productName']) ?>" required><br>
    Giá Gốc      <input type="number" name="txtPrice"    value="<?= htmlspecialchars($product['productPrice']) ?>" required min="0"><br>
    Giá Bán      <input type="number" name="txtSalePrice"value="<?= htmlspecialchars($product['salePrice']) ?>"    required min="0"><br>
    Ảnh sản phẩm <input type="file"   name="fileImage"><br>
    <img width="150" height="150" src="image/<?= htmlspecialchars($product['image']) ?>?v=<?= time() ?>"><br>
    Thương Hiệu
    <select name="txtType">
        <?php foreach ($types as $t): ?>
            <option value="<?= (int)$t['idType'] ?>" <?= $t['idType'] == $product['idType'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($t['typeName']) ?>
            </option>
        <?php endforeach; ?>
    </select><br>
    <input type="hidden" name="currentImage" value="<?= htmlspecialchars($product['image']) ?>">
    <input type="submit" name="Sua"     value="Cập nhật sản phẩm">
    <input type="reset"  name="NhapLai" value="Nhập lại">
</form>
