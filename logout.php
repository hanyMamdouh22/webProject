<?php
session_start();
session_unset();  
session_destroy(); 

if (isset($_COOKIE['username'])) {
    setcookie('username', '', time() - 3600, '/');  
}

if (isset($_COOKIE['password'])) {
    setcookie('password', '', time() - 3600, '/');  
}

header("Location: start.php"); 
exit();
?>
