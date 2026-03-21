<?php
$port = getenv('PORT') ?: '8080';
echo "Starting on port: $port\n";
shell_exec('php chat/bin/server.php > /dev/null 2>&1 &');
passthru("php -S 0.0.0.0:" . $port . " -t chat/");
