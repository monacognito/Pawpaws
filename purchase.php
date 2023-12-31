<?php

require_once(__DIR__ . "/controllers/rate_limiter.php");
require_once(__DIR__ . "/controllers/purchase_controller.php");
require_once(__DIR__ . "/controllers/helper/check_session.php");
require_once(__DIR__ . "/controllers/helper/safe_mysqli_query.php");
require_once(__DIR__ . "/controllers/helper/csrf.php");

session_start();

// If not logged in, redirect to login
check_session();

if (!isset($_SESSION['csrf_token'])) {
    generate_CSRF_token();
}

$result = NULL;
$rate_limit_exp = 0;

// Buy item
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["buy_item"])) {
    $rate_limit_exp = rate_limiter(session_id(), 60, 1, 60 * 3);
    if ($rate_limit_exp !== 0) {
        $result = "ERROR: Too many attempts. Retry in " . $rate_limit_exp - time() . " seconds";
        header("HTTP/1.1 429 Too Many Requests");
        header(sprintf("Retry-After: %d", $rate_limit_exp - time()));
    } else if (!verify_CSRF_token($_POST['csrf_token'])) {
        $result = "ERROR: CSRF token mismatch.";
        header("HTTP/1.1 403 Forbidden");
    } else {
        $buy_item = $_POST["buy_item"];
        $buy_amount = $_POST["buy_amount"];
        $result = buy_item($conn, $buy_item, $buy_amount);
    }
}

$search_error = NULL;
$search_result = NULL;
$search_result_count = NULL;

// search item
if ($_SERVER["REQUEST_METHOD"] === "GET" && isset($_GET['search'])) {
    $rate_limit_exp = rate_limiter(session_id(), 60, 1, 60 * 3);
    if ($rate_limit_exp !== 0) {
        $search_result = "ERROR: Too many attempts. Retry in " . $rate_limit_exp - time() . " seconds";
        header("HTTP/1.1 429 Too Many Requests");
        header(sprintf("Retry-After: %d", $rate_limit_exp - time()));
    } else {
        $keyword = htmlspecialchars(trim($_GET["keyword"]));
        [$search_result, $search_result_count] = search_item($conn, $keyword);
    }
}

// Get items
$query_all_items = "select * from items order by name;";
$result_all_items = safe_mysqli_query($conn, $query_all_items);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/navbar.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/purchase.css">
    <title>Purchase</title>
</head>
<body>
<div class="navbar-container">
    <div class="navbar-row">
        <div class="navbar-left cg-10px">
            <a href="dashboard.php" class="navbar-item">Dashboard</a>
            <a href="grooming.php" class="navbar-item">Grooming</a>
            <a href="purchase.php" class="navbar-item navbar-on">Purchase</a>
            <a href="membership.php" class="navbar-item">Membership</a>
        </div>
        <a href="logout.php" class="navbar-item">Logout</a>
    </div>
</div>
<div>

    <div class="flex">
        <div class="flex-50 padding-10px center-child-horizontal flex-col">
            <h2>All items</h2>
            <div class="container-items flex-col">
                <?php echo $result; ?>
                <ul>
                    <?php
                    if (mysqli_num_rows($result_all_items)) {
                        $sn = 1;
                        while ($data = mysqli_fetch_assoc($result_all_items)) {
                            ?>
                            <li>
                                <div class="flex-col">
                                    <div class="flex-row justify-between">
                                        <b><?php echo $data['name']; ?></b>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                                            <label>
                                                Amount:
                                                <input class="input-number" type="number" required="required"
                                                       name="buy_amount" min="0" max=<?php echo $data['stock'] ?> min
                                            </label>
                                            <input class="pay-button" type="submit" name="buy_item" value="Purchase">
                                            <input type="hidden" name="buy_item" value=<?php echo $data['id'] ?>>
                                        </form>
                                    </div>
                                    <div class="description"><?php echo $data['description'] ?></div>
                                </div>
                                <div>ID <?php echo $data['id']; ?> | IDR <?php echo $data['price']; ?> | (<?php echo $data['stock']; ?> In stock)</div>
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
        <div class="flex-50 padding-10px flex-col">
            <div class="flex-col">
                <div><b>Search Item</b></div>
                <div>
                    <form method="get" action="purchase.php">
                        <div>
                            <label>
                                Search
                                <input type="text" placeholder="search here..." name="keyword" required="required"
                                       value="<?php echo $_GET['keyword'] ?? NULL ?>"/>
                            </label>
                            <button name="search">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                <?php if ($search_result_count === NULL) {
                    // NULL count means error, so display error message
                    echo $search_result;
                } else if (!empty($keyword)) {
                    // Display user's input
                    echo "Result for \"" . $keyword . "\":";
                } else {
                    // User hasn't input anything
                    echo "Result will be displayed below";
                }
                ?>
            </div>
            <div class="container-items flex-col">
                <ul>
                    <?php
                    if ($search_result_count) {
                        $sn = 1;
                        while ($data = mysqli_fetch_assoc($search_result)) {
                            ?>
                            <li>
                                <div class="flex-col">
                                    <div class="flex-row justify-between">
                                        <b><?php echo $data['name']; ?></b>
                                        <form method="post" style="display:inline;">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
                                            <label>
                                                Amount to buy
                                                <input class="input-number" type="number" required="required"
                                                       name="buy_amount" min="0" max=<?php echo $data['stock'] ?>>
                                            </label>
                                            <input class="pay-button" type="submit" name="buy_item"
                                                   value=<?php echo $data['id'] ?>>
                                        </form>
                                    </div>
                                    <div class="description"><?php echo $data['description'] ?></div>
                                </div>
                                <div>IDR <?php echo $data['price']; ?> | (<?php echo $data['stock']; ?> in stock)</div>
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
    </div>

</body>
</html>