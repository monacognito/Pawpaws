<?php

// Source: https://gist.github.com/freekrai/cdcd6ebb29d84b9dc244282e64caf5fe

class RateExceededException extends Exception {}

class RateLimiter {
    private string $prefix;

    public function __construct($token, $prefix = "rate") {
        $this->prefix = sha1($prefix . $token);

        if (!isset($_SESSION["cache"])) {
            $_SESSION["cache"] = array();
        }

        if (!isset($_SESSION["expires"])) {
            $_SESSION["expires"] = array();
        } else {
            $this->expireSessionKeys();
        }
    }

    public function limitRequestsInMinutes($allowedRequests, $minutes): void {
        $this->expireSessionKeys();
        $requests = 0;

        foreach ($this->getKeys($minutes) as $key) {
            $requestsInCurrentMinute = $this->getSessionKey($key);
            if ($requestsInCurrentMinute) {
                $requests += $requestsInCurrentMinute;
            }
        }

        if (!$requestsInCurrentMinute) {
            $this->setSessionKey($key, 1, ($minutes * 60 + 1));
        } else {
            $this->increment($key, 1);
        }
        if ($requests > $allowedRequests) {
            throw new RateExceededException;
        }
    }

    private function getKeys($minutes): array {
        $keys = array();
        $now = time();
        for ($time = $now - $minutes * 60; $time <= $now; $time += 60) {
            $keys[] = $this->prefix . date("dHi", $time);
        }
        return $keys;
    }

    private function increment($key, $inc): void {
        $cnt = 0;
        if (isset($_SESSION['cache'][$key])) {
            $cnt = $_SESSION['cache'][$key];
        }
        $_SESSION['cache'][$key] = $cnt + $inc;
    }

    private function setSessionKey($key, $val, $expiry): void {
        $_SESSION["expires"][$key] = time() + $expiry;
        $_SESSION['cache'][$key] = $val;
    }

    private function getSessionKey($key) {
        return $_SESSION['cache'][$key] ?? false;
    }

    private function expireSessionKeys(): void {
        foreach ($_SESSION["expires"] as $key => $value) {
            if (time() > $value) {
                unset($_SESSION['cache'][$key]);
                unset($_SESSION["expires"][$key]);
            }
        }
    }
}

function rate_limiter($token, $attempts, $minutes, $limit_duration): int {
    $rate_limiter = new RateLimiter($token);
    try {
        $rate_limiter->limitRequestsInMinutes($attempts, $minutes);
    } catch (RateExceededException $e) {
        if (!isset($_SESSION['rate_limit_exp'])) {
            $_SESSION['rate_limit_exp'] = time() + $limit_duration;
        }
        return $_SESSION['rate_limit_exp'];
    }
    return 0;
}