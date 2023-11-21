<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    require_once "controllers/connection.php";

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) 
    {
        header("location: dashboard.php");
        exit;
    }

    $username = "";
    $password = "";
    $error = "";

    if ($_SERVER["REQUEST_METHOD"] === "POST")
    {

        //Check username && password == Empty
        if (empty(trim($_POST["username"])))
        {
            $error = "Please enter username.";
        } 
        else 
        {
            $username = trim($_POST["username"]);
        }
        if (empty(trim($_POST["password"]))) 
        {
            $error = "Please enter your password.";
        } 
        else 
        {
            $password = trim($_POST["password"]);
        }

        //When there is no error
        if (empty($error))
        {
            // Using prepare statement
            $query = "select id, username, password from users where username = ?";
            
            //Sanitize
            $username = mysqli_real_escape_string($conn, $username);
            $password = mysqli_real_escape_string($conn, $password);
    
            if ($stmt = mysqli_prepare($conn, $query)) 
            {
                //Bind variables
                mysqli_stmt_bind_param($stmt, "s", $param_username);
                $param_username = $username;

                if (mysqli_stmt_execute($stmt))
                {
                    //Store result
                    mysqli_stmt_store_result($stmt);

                    // If username exist
                    if (mysqli_stmt_num_rows($stmt) == 1) 
                    {
                        mysqli_stmt_bind_result($stmt, $id, $username, $db_password);

                        if (mysqli_stmt_fetch($stmt)) 
                        {
                            // If password match
                            if ($password === $db_password)
                            {
                                session_start();

                                $_SESSION["loggedin"] = true;
                                $_SESSION["id"] = $id;
                                $_SESSION["username"] = $username;

                                // Set cookie with user information && Cookie expired in 24 hours
                                setcookie('user_id', $id, time() + 86400, '/', '', true, true);
                                #setcookie('username', $username, time() + 86400, '/', '', true, true);

                                setcookie('user_id', $id, ["samesite" => "Strict"]);
                                #setcookie('username', $username, ["samesite" => "Strict"]);

                                header("location: dashboard.php");
                            } else
                            {
                                $error = "Invalid password";
                            }
                        }
                    } else 
                    {
                        $error = "Invalid username";
                    }
                } else 
                {
                    $error = "Some error";
                }
                mysqli_stmt_close($stmt);
            }
        }
        mysqli_close($conn);
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
  <form action="login.php" method ="post">
    <h2>Admin Login</h2>
    <?php echo $error; ?>
    <br>
    <label>Username</label>
    <input type="text" name="username" placeholder="e.g Jennie"><br>
    <label>Password</label>
    <input type="password" name="password" placeholder="*********"><br> 
    <input type="submit" value="Login">
  </form>
</body>
</html>