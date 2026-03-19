<?php
require 'db.php';

header('Content-Type: application/json');

if (isset($_GET['token'])) {

    $token = trim($_GET['token']);

    $stmt = $pdo->prepare(
        "SELECT id FROM users
         WHERE verification_token = ?
           AND is_verified = 0
           AND (token_expires_at IS NULL OR token_expires_at > NOW())"
    );
    $stmt->execute([$token]);
    $user = $stmt->fetch();

    if ($user) {
       
        $update = $pdo->prepare(
            "UPDATE users
             SET is_verified = 1,
                 verification_token = NULL,
                 token_expires_at = NULL
             WHERE id = ?"
        );
        $update->execute([$user['id']]);

        echo json_encode(["status" => "success", "message" => "Email verified! You can now log in."]);
    } else {
        echo json_encode(["status" => "error", "message" => "Invalid or expired token."]);
    }

} else {
    echo json_encode(["status" => "error", "message" => "No token provided."]);
}
?>