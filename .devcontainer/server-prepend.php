<?php

// This file rewrites $_SERVER from the request's Host header so the PHP
// format's $MYSITE-built URLs work behind Codespaces / other port forwarders.

$forwardedHttps = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
$hostHeader = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';

if ($hostHeader !== '') {
    $_SERVER['HTTP_HOST'] = $hostHeader;
}

if ($forwardedHttps) {
    $_SERVER['HTTPS'] = 'on';
}
