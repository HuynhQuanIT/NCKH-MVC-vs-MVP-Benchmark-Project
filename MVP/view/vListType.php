<?php
/**
 * MVP - View: vListType (sidebar danh mục)
 * Nhận $types từ Presenter, chỉ render.
 */
?>
<?php if (empty($types)): ?>
    <span style="color:#fff;">Không có dữ liệu</span>
<?php else: ?>
    <?php foreach ($types as $r): ?>
        <a href="?idType=<?= (int)$r['idType'] ?>"><?= htmlspecialchars($r['typeName']) ?></a><br>
    <?php endforeach; ?>
<?php endif; ?>
