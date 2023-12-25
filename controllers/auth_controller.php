<?php
require_once(__DIR__ . "/connection.php");
require_once(__DIR__ . "/helper/safe_mysqli_query.php");
require_once(__DIR__ . "/helper/csrf.php");
require_once(__DIR__ . "/rate_limiter.php");

session_start();

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rate_limit_exp = rate_limiter(session_id(), 15, 1, 60 * 5);
    if ($rate_limit_exp !== 0) {
        $_SESSION['error_message'] = "ERROR: Too many attempts. Retry in " . $rate_limit_exp - time() . " seconds";
        header("HTTP/1.1 429 Too Many Requests");
        header(sprintf("Retry-After: %d", $rate_limit_exp - time()));
        header("Location: ../login.php");
        exit;
    }

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