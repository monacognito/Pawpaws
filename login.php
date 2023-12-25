<?php
require_once(__DIR__ . "/controllers/helper/csrf.php");

session_start();

if(!isset($_SESSION['csrf_token'])) {
    generate_CSRF_token();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset='utf-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <link rel='stylesheet' href='./css/login.css'>
    <link rel='stylesheet' href='./css/main.css'>
    <title>Login</title>
</head>
<body>
<form action="controllers/auth_controller.php" method="post">
    <h2>Admin Login</h2>
    <?php if (isset($_SESSION['error_message'])) {
        echo '<br>' . '<div class="error-message">' . $_SESSION['error_message'] . '</div>';
        unset($_SESSION['error_message']);
    } ?>
    <br>
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
    <label>
        Username
        <input type="text" name="username" placeholder="e.g Jennie">
    </label>
    <br>
    <label>
        Password
        <input type="password" name="password" placeholder="*********">
    </label>
    <br>
    <input type="submit" value="Login">
</form>
</body>
</html>