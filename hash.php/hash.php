<?php
$password = "widiya123"; // ganti dengan password yang ingin di-hash
$hash = password_hash($password, PASSWORD_DEFAULT);
echo $hash;
?>