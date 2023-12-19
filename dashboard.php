<?php
require_once(__DIR__."/helper/safe_mysqli_query.php");
session_start();

    // If not logged in redirect to login
    if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] != true) 
    {
        header("location: login.php");
        exit;
    }

    require_once 'controllers/connection.php';

    // Get groomings
    $query_today_groomings = "
    select
        g.id groom_id,
        m.id member_id, 
        m.name as name,
        m.type as type,
        m.gender as gender,
        m.owner_mobile as mobile,
        g.groom_date as date,
        g.groom_time as time,
        g.price,
        g.is_paid
    from groomings g
    join members m
        on g.member_id = m.id
    where groom_date = curdate()
    order by date asc, time asc;
    ";

    // Get purchases history
    $query_purchases_history = "
    select
        p.id id,
        i.id item_id, 
        i.name as name,
        p.amount as amount,
        p.total as total,
        i.stock as new_stock,
        p.created_at as created_at
    from purchases p
    join items i
        on p.item_id = i.id
    order by p.created_at desc;
    ";

    $result_today_groomings = safe_mysqli_query($conn, $query_today_groomings);
    $result_purchases_history = safe_mysqli_query($conn, $query_purchases_history);
    $error = "";

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="./css/navbar.css">
  <link rel="stylesheet" href="./css/main.css">
  <link rel="stylesheet" href="./css/dashboard.css">
  <title>Dashboard</title>
</head>
<body>
  <div class="navbar-container">
    <div class="navbar-row">
      <div class="navbar-left cg-10px">
        <a href="dashboard.php" class="navbar-item navbar-on">Dashboard</a>
        <a href="grooming.php" class="navbar-item">Grooming</a>
        <a href="purchase.php" class="navbar-item">Purchase</a>
        <a href="membership.php" class="navbar-item">Membership</a>
      </div>
      <a href="logout.php" class="navbar-item">Logout</a>
    </div>
  </div>
  <div class="flex body-container">
    <?php echo $error; ?>
    <div class="flex-40">
      <h2>Purchases History</h2>
      <div class="container-items flex-col overflow-auto h-100">
        <ul>
          <?php
          if (mysqli_num_rows($result_purchases_history)) {
            $sn = 1;
            while ($data = mysqli_fetch_assoc($result_purchases_history)) {
              ?>
                <li>
                  <div><?php echo $data['created_at']; ?></div>
                  <div>
                    <b><?php echo $data['name']; ?></b> - <?php echo $data['amount']; ?> pcs </div>
                  <div>IDR <?php echo $data['total']; ?> </div>
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
    <div class="flex-30">
      <h2>Today's groomings</h2>
      <div class="container-items flex-col overflow-auto h-100">
        <ul>
          <?php
          if (mysqli_num_rows($result_today_groomings)) {
            $sn = 1;
            while ($data = mysqli_fetch_assoc($result_today_groomings)) {
              ?>
                <li>
                  <div>Scheduled at <?php echo $data['time']; ?></div>
                  <div>(ID: <?php echo $data['groom_id']; ?>)
                    <b><?php echo $data['name']; ?></b> - <?php echo $data['type']; ?> 
                  </div>
                  <?php
                    if ($data['is_paid'] == true) {
                      echo "<div>paid</div>";
                    } else {
                      echo "<div><b>unpaid IDR ".$data['price']."</b></div>";
                    }
                    echo "<div>phone (+62) ".$data['mobile']."</div>"
                  ?>
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
  </div>
</body>
</html>