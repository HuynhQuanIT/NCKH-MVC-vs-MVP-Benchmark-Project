<?php
/**
 * MVP - View: vListProduct
 * View THUẦN TÚY - chỉ render HTML từ $products được Presenter truyền vào.
 * KHÔNG gọi Controller, KHÔNG gọi Model, KHÔNG có business logic.
 *
 * Biến nhận từ Presenter:
 *   $products  - array[] danh sách sản phẩm
 */
?>
<style>
  .price-old  { color:#c00; text-decoration:line-through; margin-right:6px }
  .price-sale { color:#000; font-weight:700 }
</style>

<?php if (empty($products)): ?>
    <p>Không có sản phẩm!</p>
<?php else: ?>
    <table>
        <tr>
        <?php $col = 0; foreach ($products as $r): ?>
            <td>
                <img src="image/<?= htmlspecialchars($r['image']) ?>" width="150" height="150" alt=""><br>
                <b><a href=""><?= htmlspecialchars($r['productName']) ?></a></b><br>
                <?php
                $price = (float)($r['productPrice'] ?? 0);
                $sale  = (float)($r['salePrice']    ?? 0);
                if ($sale > 0 && $sale < $price): ?>
                    <span class="price-old"><?= number_format($price, 0, '.', '.') ?>đ</span><br>
                    <span class="price-sale"><?= number_format($sale, 0, '.', '.') ?>đ</span>
                <?php else: ?>
                    <span class="price-sale"><?= number_format($price, 0, '.', '.') ?>đ</span>
                <?php endif; ?>
            </td>
            <?php $col++;
            if ($col % 4 === 0) echo '</tr><tr>'; ?>
        <?php endforeach; ?>
        </tr>
    </table>
<?php endif; ?>
