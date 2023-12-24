<?php
require_once(__DIR__ . "/connection.php");
require_once(__DIR__ . "/helper/safe_mysqli_query.php");
require_once(__DIR__ . "/helper/csrf.php");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // CSRF Protection
    if (!verify_CSRF_token($_POST['csrf_token'])) {
        $_SESSION['error_message'] = "ERROR: CSRF token mismatch.";
        header("Location: ../login.php");
        exit;
    }

    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error_message'] = "ERROR: Username or password cannot be empty";
        header("Location: ../login.php");
        exit;
    }

    $query = "select * from users where username = ?";
    $result = safe_mysqli_query($conn, $query, "s", [$username]);

    if ($result->num_rows === 1) {
        $row = $result->fetch_assoc();

        if (password_verify($password, $row["password"])) {
            $_SESSION["id"] = $row["id"];
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $row["username"];

            // Set cookie to last 24 hours
            setcookie('id', $_SESSION['id'], [
                'expires'  => time() + 60 * 60 * 24,
                'path'     => '/',
                'domain'   => null,
                'secure'   => true,
                'httponly' => 'true',
                'samesite' => 'Strict'
            ]);
            header("Location: ../dashboard.php");
        } else {
            $_SESSION['error_message'] = "ERROR: Username or password does not match.";
            header("Location: ../login.php");
        }
    } else {
        $_SESSION['error_message'] = "ERROR: Username or password does not match.";
        header("Location: ../login.php");
    }
}