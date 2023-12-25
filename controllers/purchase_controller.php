<?php

require_once(__DIR__ . "/connection.php");

function buy_item($db_conn, $item_id, $item_amount): string {
    // Sanity check & validation
    $amount = trim($_POST["buy_amount"]);
    if (empty($amount) || !is_numeric($amount)) {
        return "ERROR: Amount must be numerical";
    }

    $query_validate_stock = "select stock from items where id = ?;";
    if ($result_stock = safe_mysqli_query($db_conn, $query_validate_stock, "i", [$item_id])) {
        if ($result_stock->fetch_assoc()["stock"] < $item_amount) {
            return "ERROR: insufficient amount of stock";
        }

        $query_buy_item = "update items set stock = stock - ? where id = ?;";
        if (safe_mysqli_query($db_conn, $query_buy_item, "ii", [$item_amount, $item_id], false)) {
            return $item_amount . " of item ID " . $item_id . " successfully bought";
        } else {
            return "ERROR: Unknown database error while updating stock amount";
        }
    } else
        return "ERROR: Unknown database error while fetching item";
}

function search_item($db_conn, $keyword): array {
    // Sanity check (already sanitised on purchase page)
    if (empty($keyword)) {
        return ["ERROR: Search input cannot be empty", NULL];
    }

    $search_keywords = explode(' ', $keyword);
    $search_string = "select * from items where " . str_repeat("name like concat('%', ?, '%') or ", count($search_keywords));
    $search_string = substr($search_string, 0, strlen($search_string) - 4);

    $db_bind_str = str_repeat("s", count($search_keywords));
    $search_result = safe_mysqli_query($db_conn, $search_string, $db_bind_str, $search_keywords);
    $search_result_count = mysqli_num_rows($search_result);

    return [$search_result, $search_result_count];
}