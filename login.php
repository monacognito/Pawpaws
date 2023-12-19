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
<form action="controllers/AuthController.php" method="post">
    <h2>Admin Login</h2>
    <br>
    <label>Username</label>
    <input type="text" name="username" placeholder="e.g Jennie"><br>
    <label>Password</label>
    <input type="password" name="password" placeholder="*********"><br>
    <input type="submit" value="Login">
</form>
</body>
</html>