<?php

require_once(__DIR__ . "/controllers/helper/check_session.php");

session_start();

// Redirect to login page if session expires
check_session();

// Redirect to dashboard page by default
header("Location: dashboard.php");
exit;