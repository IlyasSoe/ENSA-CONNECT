cd C:\xampp\htdocs\me\myApp\ENSA-CONNECT
ls
git status

#!/bin/bash
php chat/bin/server.php &
exec php -S 0.0.0.0:$PORT -t chat/
