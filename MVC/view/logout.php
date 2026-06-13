<?php
    session_destroy();  
    echo"<script>alert('Bạn đã đăng xuất thành công')</script>";
    header("refresh:1; url=index.php");
?>
