<?php
$host = '127.0.0.1';
$dbname = 'ensa_connect'; // Ensure this matches the DB name in phpMyAdmin
$username = 'root';
$password = ''; // Default for EasyPHP is usually empty

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
