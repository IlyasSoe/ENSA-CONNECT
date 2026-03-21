#!/bin/bash
echo "PORT is: $PORT"
php chat/bin/server.php &
php -S 0.0.0.0:${PORT:-8080} -t chat/
