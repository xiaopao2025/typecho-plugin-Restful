<?php

use MoeFront\RestfulTests\Util;
use SSX\Utility\Serve;

if (php_sapi_name() !== 'cli') {
    exit;
}

// Errors on full!
ini_set('display_errors', 1);
error_reporting(E_ALL | E_STRICT);

Util::downloadTypecho();

// Start build-in server
$server = new Serve(array(
    'address' => getenv('WEB_SERVER_HOST'),
    'port' => getenv('WEB_SERVER_PORT'),
    'document_root' => dirname(__FILE__) . getenv('WEB_SERVER_DOCROOT'),
));

$server->start();

// Wait for server to be ready
$host = getenv('WEB_SERVER_HOST');
$port = getenv('WEB_SERVER_PORT');
$maxRetries = 30;
$retryInterval = 100000; // 100ms

for ($i = 0; $i < $maxRetries; $i++) {
    $fp = @fsockopen($host, $port, $errno, $errstr, 1);
    if ($fp) {
        fclose($fp);
        break;
    }
    usleep($retryInterval);
}

// Kill the web server when the process ends
register_shutdown_function(function () use ($server) {
    $server->stop();
});

Util::installTypecho();
