<?php
require_once(__DIR__."/helper/safe_mysqli_query.php");
require_once(__DIR__."/helper/grooming_actions.php");
require_once(__DIR__."/controllers/connection.php");
session_start();

// if not logged in redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true)
{
    header("location: login.php");
    exit;
}

// Get groomings
$query_unpaid_groomings = "
select
    g.id as groom_id,
    m.id as member_id, 
    m.name as name,
    m.type as type,
    m.gender as gender,
    m.owner_mobile as mobile,
    g.groom_date as date,
    g.groom_time as time,
    g.price as price
from groomings g
join members m
    on g.member_id = m.id
where is_paid = false
order by date asc, time asc;";

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
    g.price as price
from groomings g
join members m
    on g.member_id = m.id
where is_paid = true
order by date asc, time asc;";

$result_unpaid_groomings = safe_mysqli_query($conn, $query_unpaid_groomings);
$result_paid_groomings = safe_mysqli_query($conn, $query_paid_groomings);

$result = NULL;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["newGroomingSubmit"]) && $_POST["newGroomingSubmit"] === "Submit") {
        $result = submitGrooming($conn);
    }

    // Handle delete
    if (isset($_POST["deleteGrooming"])) {
        $result = deleteGrooming($_POST["deleteGrooming"], $conn);
    }

    // Handle pay
    if (isset($_POST["payGrooming"])) {
        $result = payGrooming($_POST["payGrooming"], $conn);
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
      </div>
      <a href="logout.php" class="navbar-item">Logout</a>
    </div>
  </div>
  <div class="flex">
    <div class="flex-20 padding-10px center-child-horizontal">
      <div class="container-new-form">
        <?php echo $result; ?>
        <form action="grooming.php" method="post">
            <h2>New Grooming</h2>
            <label for="member_id">ID</label>
            <input class="block" type="number" id="member_id" name="member_id" maxlength="50" placeholder="10">
            <br>
            <label for="price">Price</label>
            <input class="block" type="number" id="price" name="price" maxlength="50" placeholder="25000">
            <br>
            <label for="time">Time</label>
            <input class="block" type="time" id="time" name="time" value="10:00">
            <br>
            <label for="date">Date</label>
            <input class="block" type="date" id="date" name="date" value="<?php echo date('Y-m-d'); ?>">
            <br>
            <input type="submit" name="newGroomingSubmit" value="Submit">`
        </form>
      </div>
    </div>
    <div class="flex-40">
      <h2>Unpaid Bookings</h2>
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
                      <input class="delete-button" type="submit" name="deleteGrooming" value="Delete">
                      <input type="hidden" name="deleteGrooming" value=<?php echo $data['groom_id']?>>
                    </form>
                    <form method="post" style="display:inline;">
                      <input class="pay-button" type="submit" name="payGrooming" value="Pay">
                      <input type="hidden" name="payGrooming" value=<?php echo $data['groom_id']?>>
                    </form>
                  </div>
                </div>
                <div>IDR <?php echo $data['price']; ?> | <?php echo $data['date']; ?> </div>
              </li>  
            <?php $sn++;
          }
        } else { ?>
            <tr>
              <div colspan="8">No data found</div>
            </tr>
        <?php } ?>
      </ul>
    </div>
    <div class="flex-40">
      <h2>Paid bookings</h2>
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
              <div colspan="8">No data found</div>
            </tr>
        <?php } ?>
      </ul>
    </div>
  </div>
</body>

</html>