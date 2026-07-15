<?php
$password = "errol123"; // the plain text password
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

echo $hashedPassword;
?>
