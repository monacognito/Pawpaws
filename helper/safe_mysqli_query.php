<?php

function safe_mysqli_query(mysqli $conn, $stmt, $bind_str, ...$var) {
    $prep_stmt = $conn->prepare($stmt);
    if (isset($bind_str)) {
        $prep_stmt->bind_param($bind_str, ...$var);
    }
    $prep_stmt->execute();
    $result = $prep_stmt->get_result();
    $prep_stmt->close();
    return $result;
}