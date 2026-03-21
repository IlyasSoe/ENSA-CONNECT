<?php
$port = getenv('PORT') ?: '8080';
echo "PORT env value: " . var_export(getenv('PORT'), true) . "\n";
echo "Starting on port: $port\n";
shell_exec('php chat/bin/server.php > /dev/null 2>&1 &');
passthru("php -S 0.0.0.0:" . $port . " -t chat/");
