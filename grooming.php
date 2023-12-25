<?php

require_once(__DIR__ . "/controllers/rate_limiter.php");
require_once(__DIR__ . "/controllers/grooming_controller.php");
require_once(__DIR__ . "/controllers/helper/check_session.php");
require_once(__DIR__ . "/controllers/helper/safe_mysqli_query.php");
require_once(__DIR__ . "/controllers/helper/csrf.php");

session_start();

// if not logged in redirect to login
check_session();

if (!isset($_SESSION['csrf_token'])) {
    generate_CSRF_token();
}

$result = NULL;
$rate_limit_exp = 0;
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $rate_limit_exp = rate_limiter(session_id(), 60, 1, 60 * 3);
    if ($rate_limit_exp !== 0) {
        $result = "ERROR: Too many attempts. Retry in " . $rate_limit_exp - time() . " seconds";
        header("HTTP/1.1 429 Too Many Requests");
        header(sprintf("Retry-After: %d", $rate_limit_exp - time()));
    } else if (!verify_CSRF_token($_POST['csrf_token'])) { // CSRF token check
        $result = "ERROR: CSRF token mismatch.";
        header("HTTP/1.1 403 Forbidden");
    } else {
        // Submit grooming request
        if (isset($_POST["submit_grooming"]) && $_POST["submit_grooming"] === "Submit") {
            $result = submit_grooming($conn);
        }
        // Delete grooming request
        if (isset($_POST["delete_grooming"])) {
            $result = delete_grooming($conn, $_POST["delete_grooming"]);
        }
        // Finish (pay) grooming request
        if (isset($_POST["pay_grooming"])) {
            $result = pay_grooming($conn, $_POST["pay_grooming"]);
        }
    }
}

// Get grooming
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
from grooming g
join members m
    on g.member_id = m.id
where is_paid = false
order by date, time;";

$query_paid_groomings = "
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
from grooming g
join members m
    on g.member_id = m.id
where is_paid = true
order by date, time;";

$result_unpaid_groomings = safe_mysqli_query($conn, $query_unpaid_groomings);
$result_paid_groomings = safe_mysqli_query($conn, $query_paid_groomings);
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
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
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
                <input type="submit" name="submit_grooming" value="Submit">`
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
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                                    <input class="delete-button" type="submit" name="delete_grooming" value="Delete">
                                    <input type="hidden" name="delete_grooming" value=<?php echo $data['groom_id'] ?>>
                                </form>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                                    <input class="pay-button" type="submit" name="pay_grooming" value="Pay">
                                    <input type="hidden" name="pay_grooming" value=<?php echo $data['groom_id'] ?>>
                                </form>
                            </div>
                        </div>
                        <div>IDR <?php echo $data['price']; ?> | <?php echo $data['date']; ?> </div>
                    </li>
                    <?php $sn++;
                }
            } else { ?>
                <tr>
                    <td colspan="8">No data found</td>
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
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                                <input class="delete-button" type="submit" name="delete_grooming" value="Delete">
                                <input type="hidden" name="delete_grooming" value=<?php echo $data['groom_id']; ?>>
                            </form>
                        </div>
                        <div>IDR <?php echo $data['price']; ?> | <?php echo $data['date']; ?> </div>
                    </li>
                    <?php $sn++;
                }
            } else { ?>
                <tr>
                    <td colspan="8">No data found</td>
                </tr>
            <?php } ?>
        </ul>
    </div>
</div>
</body>

</html>