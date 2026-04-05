<?php

require '/app/vendor/autoload.php';
require 'db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["error" => "Invalid request method."]);
    exit();
}

$email     = isset($_POST['email'])    ? trim($_POST['email'])    : '';
$pass      = isset($_POST['password']) ? trim($_POST['password']) : '';
$user_name = isset($_POST['username']) ? trim($_POST['username']) : '';
$role_id   = 1; // Default: Etudiant (1=Etudiant, 2=Lauréat, 3=Mentor, 4=Admin)

if (empty($email) || empty($pass) || empty($user_name)) {
    echo json_encode(["error" => "All fields are required."]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["error" => "Invalid email format."]);
    exit();
}

if (substr($email, -10) !== '@uca.ac.ma') {
    echo json_encode(["error" => "Access Denied: Please use your @uca.ac.ma email."]);
    exit();
}

$hashedPassword = password_hash($pass, PASSWORD_BCRYPT);

$token = sha1(uniqid(mt_rand(), true) . uniqid(mt_rand(), true));

try {
    $stmt = $pdo->prepare(
        "INSERT INTO users (email, password_hash, username, role_id, verification_token, is_verified, token_expires_at)
        VALUES (?, ?, ?, ?, ?, 0, DATE_ADD(NOW(), INTERVAL 24 HOUR))"
    );
    $stmt->execute([$email, $hashedPassword, $user_name, $role_id, $token]);

    $brevo = new Brevo\Brevo(getenv('BREVO_API_KEY'));

    $sender = new Brevo\TransactionalEmails\Types\SendTransacEmailRequestSender([
        'email' => 'isoi.ily22@gmail.com',
        'name'  => 'ENSA Connect'
    ]);
    
    $recipient = new Brevo\TransactionalEmails\Types\SendTransacEmailRequestToItem([
        'email' => $email,
        'name'  => $user_name
    ]);
    
    $brevo->transactionalEmails->sendTransacEmail(
        new Brevo\TransactionalEmails\Requests\SendTransacEmailRequest([
            'sender'      => $sender,
            'to'          => [$recipient],
            'subject'     => 'Vérification de votre compte ENSA Connect',
            'textContent' => "Bonjour $user_name,\n\nCliquez ici pour vérifier votre compte :\nhttps://ensa-connect-production.up.railway.app/API/AUTH/verify.php?token=$token\n\nCe lien expire dans 24h.",
        ])
    );
    
    echo json_encode(["success" => "Compte créé pour $user_name ! Vérifiez votre email."]);

} catch (\Exception $e) {
    echo json_encode(["error" => "Email non envoyé : " . $e->getMessage()]);
} catch (PDOException $e) {
    echo json_encode(["error" => "Email or Username already exists."]);
}
?>
