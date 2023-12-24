<?php

function check_session(): void {
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
        header("Location: logout.php");
        exit;
    }

}