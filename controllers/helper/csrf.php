<?php

function generate_CSRF_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $CSRF_token = sha1(openssl_random_pseudo_bytes(32));
        $_SESSION['csrf_token'] = $CSRF_token;
    } else {
        $CSRF_token = $_SESSION['csrf_token'];
    }
    return $CSRF_token;
}

function verify_CSRF_token($CSRF_token = NULL) {
    $CSRF_token = $CSRF_token ?? $_POST['csrf_token'];
    return hash_equals($_SESSION['csrf_token'], $CSRF_token);
}
