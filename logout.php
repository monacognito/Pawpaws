<?php

session_start();
$_SESSION = array();
session_destroy();

header("Location: login.php");

unset($_COOKIE["user_id"]);
setcookie("user_id", null, time() - 60, "/");
exit;