<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
    session_start();

    require_once "connection.php";

    if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) 
    {
        header("location: ../dashboard.php");
        exit;
    }else{
        header("location: ../login.php");
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
            $password = $_POST["password"];
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

                                header("location: ../dashboard.php");
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