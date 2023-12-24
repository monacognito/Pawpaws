<?php

function create_membership($db_conn): string {
    $name = htmlspecialchars(trim($_POST["name"]));
    $breed = htmlspecialchars(trim($_POST["type"]));
    $gender = trim($_POST['gender']);
    $owner_mobile = trim($_POST["owner_mobile"]);
    $address = htmlspecialchars(trim($_POST["address"]));

    // Sanity check & validation
    $error = NULL;
    if (empty($name) || !ctype_alpha(str_replace(' ', '', $name))) {
        $error .= "ERROR: Name must contain only alphabetical characters" . nl2br("\n");
    }
    if (empty($breed) || !ctype_alpha(str_replace(' ', '', $breed))) {
        $error .= "ERROR: Pet breed must contain only alphabetical characters" . nl2br("\n");
    }
    if (empty($gender) || !($gender === "m" XOR $gender === "f")) {
        $error .= "ERROR: Pet gender must be male or female" . nl2br("\n");
    }
    if (empty($owner_mobile) || !preg_match('/^[0-9]{0,14}+$/', $owner_mobile)) {
        $error .= "ERROR: Mobile number must be within 14 digits" . nl2br("\n");
    }
    if (empty($address) || !ctype_alnum(str_replace(' ', '', $address))) {
        $error .= "ERROR: Address must contain only alphanumeric characters" . nl2br("\n");
    }
    if ($error !== NULL) {
        return $error;
    }

    $query_new_member = "insert into members value (default, ?, ?, ?, ?, ?, default, default);";
    if (safe_mysqli_query($db_conn, $query_new_member, "sssss", [$name, $breed, $gender, $owner_mobile, $address], false)) {
        $result = "Successfully created member \"" . $name . "\"";
    } else {
        $result = "ERROR: Unknown database error occurred.";
    }

    return $result;
}

function deleteMember($id_delete, $conn_delete) {
    $query_delete_member = "delete from members where id = ?;";
    if (safe_mysqli_query($conn_delete, $query_delete_member, "i", [$id_delete], false)) {
        $result = "Successfully deleted member with ID " . $id_delete;
        header("location: membership.php");
    } else {
        $result = "ERROR: Unknown database error occurred";
    }

    return $result;
}

function markMemberPaid($id_extend, $conn_extend) {
    $query_extend_member = "update members set expired_at = date_add(now(), interval 6 month) where id = ?;";
    if (safe_mysqli_query($conn_extend, $query_extend_member, "i", [$id_extend], false)) {
        $result = "Successfully extended member with ID " . $id_extend;
        header("location: membership.php");
    } else {
        $result = "ERROR: Unknown database error occurred";
    }

    return $result;
}