<?php
echo "Starting...\n";
shell_exec('php chat/bin/chat-server.php > /dev/null 2>&1 &');
sleep(1);
echo "Running web server on 8080\n";
passthru("php -S 0.0.0.0:8080");
