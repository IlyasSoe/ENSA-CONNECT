<?php
$port = getenv('PORT') ?: '8080';
echo "PORT: $port\n";

// Lancer Ratchet en arrière-plan sur port fixe
shell_exec('php chat/bin/chat-server.php > /dev/null 2>&1 &');

// Attendre que Ratchet démarre
sleep(1);

// Lancer serveur web
$cmd = "php -S 0.0.0.0:" . intval($port) . " -t chat/";
echo "Running: $cmd\n";
passthru($cmd);
