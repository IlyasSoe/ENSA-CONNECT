cd C:\xampp\htdocs\me\myApp\ENSA-CONNECT
ls
git status
```

Est-ce que tu vois `start.sh` dans la liste ? Si non, recrée-le manuellement avec **Notepad** :

1. Ouvre Notepad
2. Colle exactement :
```
#!/bin/bash
php chat/bin/server.php &
exec php -S 0.0.0.0:$PORT -t chat/
