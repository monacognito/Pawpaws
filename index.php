<?php

    session_start();

    require_once "controllers/AuthController.php";

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true)
    {
        header("location: dashboard.php");
        exit;
    } else
    {
        header("location: login.php");
        exit;
    }
?>