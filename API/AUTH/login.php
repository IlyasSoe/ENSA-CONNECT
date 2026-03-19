<?php
session_start();
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = isset($_POST['email'])    ? trim($_POST['email']) : '';
    $pass  = isset($_POST['password']) ? $_POST['password']    : '';

    if (empty($email) || empty($pass)) {
        echo json_encode(["status" => "error", "message" => "Email and password are required."]);
        exit();
    }

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password_hash'])) {

        if (!$user['is_verified']) {
            echo json_encode(["status" => "error", "message" => "Please verify your email before logging in."]);
            exit();
        }

        session_regenerate_id(true);

        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        echo json_encode(["status" => "success", "message" => "Logged in!"]);

    } else {
        echo json_encode(["status" => "error", "message" => "Invalid email or password."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "Invalid request method."]);
}
?>
