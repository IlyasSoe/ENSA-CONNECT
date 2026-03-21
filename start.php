<?php
$port = getenv('WEB_PORT') ?: '8080';
echo "PORT: $port\n";
shell_exec('php chat/bin/chat-server.php > /dev/null 2>&1 &');
sleep(1);
passthru("php -S 0.0.0.0:" . intval($port) . " -t chat/");
