<?php

// PHP's built-in server fills SERVER_NAME / SERVER_PORT from the -S bind
// address, so they're "0.0.0.0" / "8080" regardless of how the browser
// reached us. web-php builds $MYSITE from those, which then leaks into
// every stylesheet/script/icon URL and breaks the page when accessed
// through Codespaces port forwarding (or any other proxy).

$forwardedHttps = ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
$hostHeader = $_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '';

if ($hostHeader !== '') {
    $parts = parse_url('//' . $hostHeader);

    if (!empty($parts['host'])) {
        $_SERVER['SERVER_NAME'] = $parts['host'];
        $_SERVER['SERVER_PORT'] = (int)($parts['port']
            ?? $_SERVER['HTTP_X_FORWARDED_PORT']
            ?? ($forwardedHttps ? 443 : 80));
    }
}

if ($forwardedHttps) {
    $_SERVER['HTTPS'] = 'on';
}
