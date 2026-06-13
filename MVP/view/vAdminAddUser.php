<?php
/**
 * MVP - View: vAdminAddUser
 * Nhận $roles[] từ Presenter, render form tạo người dùng.
 */
?>
<form action="" method="POST">
    Username: <input type="text"     name="txtusername" placeholder="Username" required><br>
    Password: <input type="password" name="txtpassword" placeholder="Password" required><br>
    Role
    <select name="txtRole">
        <?php foreach ($roles as $r): ?>
            <option value="<?= (int)$r['idRole'] ?>"><?= htmlspecialchars($r['nameRole']) ?></option>
        <?php endforeach; ?>
    </select><br>
    <input type="submit" name="btnThem" value="Tạo người dùng">
</form>
