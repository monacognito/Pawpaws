<?php
require_once(__DIR__ . "/controllers/connection.php");
require_once(__DIR__ . "/helper/safe_mysqli_query.php");
session_start();

// If not logged in redirect to login
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

function buyItem($id_buy, $amount_buy, $conn_buy)
{
    $query_validate_stock = "select stock from items where id = ?;";
    if ($result_stock = safe_mysqli_query($conn_buy, $query_validate_stock, "i", [$id_buy])) {
        if ($result_stock->fetch_assoc()["stock"] < $amount_buy) {
            return "unavailable stock";
        }

        $query_buy_item = "update items set stock = stock - ? where id = ?;";
        if (safe_mysqli_query($conn_buy, $query_buy_item, "ii", [$amount_buy, $id_buy], false)) {
            header("Refresh:0");
            return null;
        }

        return "sql error";
    } else
        return "sql error";
}

// Get items
$query_all_items = "select * from items order by name;";
$result_all_items = safe_mysqli_query($conn, $query_all_items);

$error = NULL;

// Buy item
if (array_key_exists("buyItem", $_POST)) {
    $amount = trim($_POST["buyAmount"]);
    if (empty($amount) || !is_numeric($amount)) {
        $error = "Amount must be numerical";
    }
    $error = buyItem($_POST["buyItem"], $amount, $conn);
}

$result_search_count = 0;
$keywords_all = "";
$error_search = NULL;
// search item
if (isset($_GET['search'])) {
    // Sanity check & validation
    $keyword = htmlspecialchars(trim($_GET["keyword"]));
    if (empty($keyword)) {
        $error_search = "ERROR: Search input cannot be empty";
    }

    $search_string = "select * from items where ";
    $keywords_all = array();

    $search_keywords = explode(' ', $keyword);
    foreach ($search_keywords as $word) {
        $search_string .= "name like ? or ";
        $keywords_all[] = $word;
    }

    $search_string = substr($search_string, 0, strlen($search_string) - 4);
    $result_search = safe_mysqli_query($conn, $search_string, str_repeat("s", count($search_keywords)), $keywords_all);
    $result_search_count = mysqli_num_rows($result_search);
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
            <?php echo $error; ?>
            <h2>All items</h2>
            <div class="container-items flex-col">
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
                                            <input class="input-number" type="number" required="required" name="buy_amount" max=<?php echo $data['stock'] ?> min = 0>
                                            <input class="pay-button" type="submit" name="buy_item" value="Purchase">
                                            <input type="hidden" name="buy_item" value=<?php echo $data['id'] ?>>
                                        </form>
                                    </div>
                                    <div class="description"><?php echo $data['description'] ?></div>
                                </div>
                                <div>IDR <?php echo $data['price']; ?> | (<?php echo $data['stock']; ?> In stock)</div>
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
        <div class="flex-50 padding-10px flex-col">
            <div class="flex-col">
                <div><b>Search Item</b></div>
                <div>
                    <form method="get" action="purchase.php">
                        <div>
                            <input type="text" placeholder="search here..." name="keyword" required="required"
                                   value="<?php echo isset($_POST['keyword']) ? $_POST['keyword'] : '' ?>"/>
                            <button name="search">Search</button>
                        </div>
                    </form>
                </div>
            </div>
            <div>
                <?php if (!empty($error_search)) {
                    echo $error_search;
                } else if (!empty($keyword)) {
                    echo "results for \"" . $keyword . "\"";
                } else {
                    echo "result will be displayed below";
                }
                ?>
            </div>
            <div class="container-items flex-col">
                <ul>
                    <?php
                    if ($result_search_count) {
                        $sn = 1;
                        while ($data = mysqli_fetch_assoc($result_search)) {
                            ?>
                            <li>
                                <div class="flex-col">
                                    <div class="flex-row justify-between">
                                        <b><?php echo $data['name']; ?></b>
                                        <form method="post" style="display:inline;">
                                            <input class="input-number" type="number" required="required"
                                                   name="buyAmount" max=<?php echo $data['stock'] ?> min = 0>
                                            <input class="pay-button" type="submit" name="buyItem"
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
                            <div colspan="8">No data found</div>
                        </tr>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>

</body>
</html>