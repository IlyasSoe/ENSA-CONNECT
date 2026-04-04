<?php
var_dump(extension_loaded('pdo_mysql'));
var_dump(PDO::getAvailableDrivers());

require 'db.php';
echo "Connexion réussie !";
?>
