<?php

function safe_mysqli_query(mysqli $conn, $stmt, $types = NULL, $vars = NULL, $expect_result = true): mysqli_result|bool {
    $prep_stmt = $conn->prepare($stmt);
    if (isset($types) && isset($vars)) {
        $prep_stmt->bind_param($types, ...$vars);
    }

    $result = $prep_stmt->execute();
    if ($expect_result) {
        $result = $prep_stmt->get_result();
    }

    $prep_stmt->close();
    return $result;
}