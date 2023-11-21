<?php
    session_start();

    // if not logged in redirect to login
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) 
    {
        header("location: login.php");
        exit;
    }

    require_once 'controllers/connection.php';

    // Get groomings
    $query_unpaid_groomings = "
    select
        g.id groom_id,
        m.id member_id, 
        m.name as name,
        m.type as type,
        m.gender as gender,
        m.owner_mobile as mobile,
        g.groom_date as date,
        g.groom_time as time,
        g.price
    from groomings g
    join members m
        on g.member_id = m.id
    where is_paid = false
    order by date asc, time asc;
    ";

    $query_paid_groomings = "
    select
        g.id groom_id,
        m.id member_id, 
        m.name as name,
        m.type as type,
        m.gender as gender,
        m.owner_mobile as mobile,
        g.groom_date as date,
        g.groom_time as time,
        g.price
    from groomings g
    join members m
        on g.member_id = m.id
    where is_paid = true
    order by date asc, time asc;
    ";

    $result_unpaid_groomings = mysqli_query($conn, $query_unpaid_groomings);
    $result_paid_groomings = mysqli_query($conn, $query_paid_groomings);

    $new_submit = isset($_POST["newGroomingSubmit"]) ? $_POST["newGroomingSubmit"] : '';
    $submit_result = "";
    $error = "";

    if ($new_submit === "submit") {
    $member_id = $date = $time = $price = $paid =  "";

    // Check all
    $member_id = (empty(trim($_POST["member_id"])) ? "" : trim($_POST["member_id"]));
    $error = (empty(trim($_POST["member_id"])) ? "member_id cannot be empty" : "");
    $date = (empty(trim($_POST["date"])) ? "" : trim($_POST["date"]));
    $error = (empty(trim($_POST["date"])) ? "date cannot be empty" : "");
    $time = (empty(trim($_POST["time"])) ? "" : trim($_POST["time"]));
    $error = (empty(trim($_POST["time"])) ? "time cannot be empty" : "");
    $price = (empty(trim($_POST["price"])) ? "" : trim($_POST["price"]));
    $error = (empty(trim($_POST["price"])) ? "price cannot be empty" : "");

    // Payment status
    $paid = (empty($_POST["paid"]) ? "false" : trim($_POST["paid"]));

    // All valid
    if (empty($error))
    {
        // Check member_id
        $query_member_id_check = "select id from members where id = ".$member_id." and expired_at >= now();";
        if ($result_member_id_check = mysqli_query($conn, $query_member_id_check)) {
        if (mysqli_num_rows($result_member_id_check))
        {
            $query_new_grooming = "insert into groomings value ("
            . "default,\""
            . $member_id . "\","
            . "default,\""
            . $date . "\",\""
            . $time . "\",\""
            . $price . "\","
            . $paid . ");";

            if (mysqli_query($link, $query_new_grooming))
            {
                $submit_result = "Success adding groom for " . $member_id;
                header("location: grooming.php");
            } else
            {
                $submit_result = "An error occured.";
            }
        } else 
        {
            // Invalid id or inactive
            $submit_result = "please enter correct id and make sure membership is active"; 
        }
        }
    } else 
    {
        $submit_result = "sql error.";
    }
    }

    // Handle delete
    if (array_key_exists("deleteGrooming", $_POST))
    {
        handleDeleteGrooming($_POST["deleteGrooming"], $link);
    }

    function handleDeleteGrooming($id_delete, $conn_delete) 
    {
        $query_delete_grooming = "delete from groomings where id=".$id_delete.";";
        if (mysqli_query($conn_delete, $query_delete_grooming)) 
        {
            header("location: grooming.php");
        } else 
        {
            $error = "cannot delete";
        }
    }

    // Handle pay
    if (array_key_exists("payGrooming", $_POST))
    {
        handlePayGrooming($_POST["payGrooming"], $conn);
    }

    function handlePayGrooming($id_pay, $link_pay) 
    {
        $query_extend_member = "update groomings set is_paid = true where id=".$id_pay.";";
    if (mysqli_query($link_pay, $query_extend_member))
    {
        header("location: grooming.php");
    } else 
    {
        $error = "cannot pay"; 
    }
    } 
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/navbar.css">
  <link rel="stylesheet" href="./css/main.css">
  <link rel="stylesheet" href="./css/grooming.css">
  
  <title>Grooming</title>
</head>
<body>
  <div class="navbar-container">
    <div class="navbar-row">
      <div class="navbar-left cg-10px">
        <a href="dashboard.php" class="navbar-item">Dashboard</a>
        <a href="grooming.php" class="navbar-item navbar-on">Grooming</a>
        <a href="purchase.php" class="navbar-item">Purchase</a>
        <a href="membership.php" class="navbar-item">Membership</a>
        <a href="debug.php" class="navbar-debug">Debug</a>
        <div class="h-20px flex-row mt-10px">
          <input class="delete-button" style="height:30px" type="submit" name="" value="0">
          <div>-delete</div>
        </div>
        <div class="h-20px flex-row mt-10px">
          <input class="pay-button" style="height:30px" type="submit" name="" value="0">
          <div>-mark as paid</div>
        </div>
      </div>
      <a href="logout.php" class="navbar-item">Logout</a>
    </div>
  </div>
  <div class="flex">
    <div class="flex-20 padding-10px center-child-horizontal">
      <div class="container-new-form">
        <?php echo $submit_result; ?>
        <?php echo $error; ?>
        <form action="grooming.php" method ="post">
          <h2>new grooming</h2>
          <label>member_id</label>
          <input class="block" type="number" name="member_id" maxlength="50" placeholder="10">
          <label>price</label>
          <input class="block" type="number" name="price" maxlength="50" placeholder="25000">
          <label>time</label>
          <input class="block" type="time" name="time" value="10:00">
          <label>date</label>
          <input class="block" type="date" name="date" value="<?php echo date('Y-m-d'); ?>">
          <input type="submit" name="newGroomingSubmit" value="submit">
        </form>
      </div>
    </div>
    <div class="flex-40">
      <h2>unpaid bookings</h2>
      <ul>
        <?php
        if (mysqli_num_rows($result_unpaid_groomings)) {
          $sn = 1;
          while ($data = mysqli_fetch_assoc($result_unpaid_groomings)) {
            ?>
              <li class="flex-col">
                <div class="flex-row justify-between">
                  <div>(ID: <?php echo $data['groom_id']; ?>)
                    <b><?php echo $data['name']; ?></b> - <?php echo $data['type']; ?> 
                  </div>
                  <div>  
                    <form method="post" style="display:inline;">
                      <input class="delete-button" type="submit" name="deleteGrooming" value=<?php echo $data['groom_id']?>>
                    </form>
                    <form method="post" style="display:inline;">
                      <input class="pay-button" type="submit" name="payGrooming" value=<?php echo $data['groom_id']?>>
                    </form>
                  </div>
                </div>
                <div>IDR <?php echo $data['price']; ?> | <?php echo $data['date']; ?> </div>
              </li>  
            <?php $sn++;
          }
        } else { ?>
            <tr>
              <div colspan="8">no data found</div>
            </tr>
        <?php } ?>
      </ul>
    </div>
    <div class="flex-40">
      <h2>paid bookings</h2>
      <ul>
        <?php
        if (mysqli_num_rows($result_paid_groomings)) {
          $sn = 1;
          while ($data = mysqli_fetch_assoc($result_paid_groomings)) {
            ?>
              <li class="flex-col">
                <div class="flex-row justify-between">
                  <div>(ID: <?php echo $data['groom_id']; ?>)
                    <b><?php echo $data['name']; ?></b> - <?php echo $data['type']; ?> 
                  </div>
                  <form method="post" style="display:inline;">
                    <input class="delete-button" type="submit" name="deleteGrooming" value=<?php echo $data['groom_id']?>>
                  </form>
                </div>
                <div>IDR <?php echo $data['price']; ?> | <?php echo $data['date']; ?> </div>
              </li>  
            <?php $sn++;
          }
        } else { ?>
            <tr>
              <div colspan="8">no data found</div>
            </tr>
        <?php } ?>
      </ul>
    </div>
  </div>
</body>

</html>