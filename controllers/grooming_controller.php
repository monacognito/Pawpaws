<?php

require_once(__DIR__ . "/connection.php");

function submit_grooming($db_conn) {
    $member_id = trim($_POST["member_id"]);
    $price = trim($_POST["price"]);
    $date = trim($_POST["date"]);
    $time = trim($_POST["time"]);

    // Sanity check & validation
    $error = NULL;
    if (empty($member_id) || !is_numeric($member_id)) {
        $error .= "ERROR: Member ID must be numerical" . nl2br("\n");
    }
    if (empty($price) || !is_numeric($price)) {
        $error .= "ERROR: Price must be numerical" . nl2br("\n");
    }
    if (empty($date)) {
        $error .= "ERROR: Date cannot be empty" . nl2br("\n");
    }
    if (empty($time)) {
        $error .= "ERROR: Member ID must be numerical" . nl2br("\n");
    }
    if ($error !== NULL) {
        return $error;
    }

    // Check member ID
    $query_member_id_check = "select id from members where id = ? and expired_at >= now();";
    $result_member_id_check = safe_mysqli_query($db_conn, $query_member_id_check, "i", [$member_id]);
    if (mysqli_num_rows($result_member_id_check)) {
        $query_new_grooming = "insert into grooming value (default, ?, default, ?, ?, ?, ?);";
        if (safe_mysqli_query($db_conn, $query_new_grooming, "issii", [$member_id, $date, $time, $price, 0], false)) {
            $result = "Submission successful for ID " . $member_id;
        } else {
            $result = "ERROR: Unknown database error occurred";
        }
    } else {
        $result = "ERROR: Invalid ID. Make sure membership status is active";
    }

    return $result;
}

function pay_grooming($db_conn, $member_id) {
    $query_extend_member = "update grooming set is_paid = true where id = ?;";
    if (safe_mysqli_query($db_conn, $query_extend_member, "i", [$member_id], false)) {
        $result = "Payment successful";
        echo "<meta http-equiv='refresh' content='0'>";
    } else {
        $result = "Cannot pay";
    }

    return $result;
}

function delete_grooming($db_conn, $member_id) {
    $query_delete_grooming = "delete from grooming where id = ?;";
    if (safe_mysqli_query($db_conn, $query_delete_grooming, "i", [$member_id], false)) {
        $result = "Deletion successful";
    } else {
        $result = "cannot delete";
    }

//    echo "<script type='text/javascript'>alert('$result');</script>";
//    echo "<meta http-equiv='refresh' content='0'>";
    return $result;
}