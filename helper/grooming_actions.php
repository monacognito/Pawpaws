<?php

function submitGrooming($conn)
{

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
//        echo "<script type='text/javascript'>alert('$error');</script>";
//        echo "<meta http-equiv='refresh' content='0'>";
        return $error;
    }

    // Check member ID
    $query_member_id_check = "select id from members where id = ? and expired_at >= now();";
    $result_member_id_check = safe_mysqli_query($conn, $query_member_id_check, "i", [$member_id]);
    if (mysqli_num_rows($result_member_id_check)) {
        $query_new_grooming = "insert into groomings value (default, ?, default, ?, ?, ?, ?);";
        if (safe_mysqli_query($conn, $query_new_grooming, "issii", [$member_id, $date, $time, $price, 0], false)) {
            $result = "Submission successful for ID " . $member_id;
        } else {
            $result = "ERROR: Unknown database error occurred";
        }
    } else {
        $result = "ERROR: Invalid ID. Make sure membership status is active";
    }

//    echo "<script type='text/javascript'>alert('$result');</script>";
//    echo "<meta http-equiv='refresh' content='0'>";
    return $result;
}

function payGrooming($id_pay, $conn_pay)
{
    $query_extend_member = "update groomings set is_paid = true where id = ?;";
    if (safe_mysqli_query($conn_pay, $query_extend_member, "i", [$id_pay], false)) {
        $result = "Payment successful";
        echo "<meta http-equiv='refresh' content='0'>";
    } else {
        $result = "Cannot pay";
    }

//    echo "<script type='text/javascript'>alert('$result');</script>";
//    echo "<meta http-equiv='refresh' content='0'>";
    return $result;
}

function deleteGrooming($id_delete, $conn_delete)
{
    $query_delete_grooming = "delete from groomings where id = ?;";
    if (safe_mysqli_query($conn_delete, $query_delete_grooming, "i", [$id_delete], false)) {
        $result = "Deletion successful";
    } else {
        $result = "cannot delete";
    }

//    echo "<script type='text/javascript'>alert('$result');</script>";
//    echo "<meta http-equiv='refresh' content='0'>";
    return $result;
}