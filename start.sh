#!/bin/bash
echo "PORT is: $PORT"
php chat/bin/server.php &
exec php -S 0.0.0.0:$PORT -t chat/
