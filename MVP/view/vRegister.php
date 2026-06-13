<?php
/**
 * MVP - View: vRegister
 * Form đăng ký thuần túy. Kết quả ($registerError) do Presenter truyền vào.
 */
?>
<form action="#" method="post">
    Username: <input type="text"     name="txtusername" placeholder="Username" required><br>
    Password: <input type="password" name="txtpassword" placeholder="Password" required><br>
    <input type="submit" name="btnsubmit" value="Đăng ký">
</form>
<?php if (isset($registerError) && $registerError): ?>
    <script>alert('Đăng ký không thành công')</script>
<?php endif; ?>
