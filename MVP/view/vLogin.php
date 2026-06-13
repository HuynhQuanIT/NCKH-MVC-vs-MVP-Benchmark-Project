<?php
/**
 * MVP - View: vLogin
 * Chỉ hiển thị form. Kết quả login ($loginResult, $loginError) do Presenter xử lý trước.
 */
?>
<form action="#" method="post">
    Username: <input type="text"     name="txtusername" placeholder="Username" required><br>
    Password: <input type="password" name="txtpassword" placeholder="Password" required><br>
    <input type="submit" name="btnsubmit" value="Đăng nhập">
</form>
<?php if (isset($loginError) && $loginError): ?>
    <script>alert('Đăng nhập không thành công')</script>
<?php endif; ?>
