<?php
/**
 * POST /api/posts
 * Crée un nouveau post (texte + fichier optionnel).
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Méthode non autorisée. Utilisez POST.'));
    exit;
}

require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../middleware/XSSProtection.php';
require_once __DIR__ . '/../../middleware/RateLimiter.php';

try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}

// Récupérer l'utilisateur connecté
$userId = isset($_POST['user_id']) ? (int)$_POST['user_id'] : 0;

if ($userId === 0) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'error' => 'Utilisateur non authentifié.'));
    exit;
}

// Rate Limiting : max 10 posts/heure
$rateLimiter = new RateLimiter($db);

if (!$rateLimiter->isAllowed($userId)) {
    http_response_code(429);
    echo json_encode(array(
        'success' => false,
        'error'   => 'Limite atteinte : maximum 10 posts par heure.',
        'retry_after' => '1 heure'
    ));
    exit;
}

// Validation & nettoyage XSS
$rawContent = isset($_POST['content']) ? $_POST['content'] : '';
$validation = XSSProtection::validatePostContent($rawContent);

if (!$validation['valid']) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'error' => $validation['error']));
    exit;
}

$content  = $validation['content'];
$filePath = null;

// Gestion de l'upload de fichier
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

    $allowedTypes = array('image/jpeg', 'image/png', 'image/gif', 'application/pdf');
    $maxSize      = 5 * 1024 * 1024; // 5 MB

    $fileType = mime_content_type($_FILES['file']['tmp_name']);
    $fileSize = $_FILES['file']['size'];

    if (!in_array($fileType, $allowedTypes)) {
        http_response_code(422);
        echo json_encode(array('success' => false, 'error' => 'Type de fichier non autorisé. (jpg, png, gif, pdf)'));
        exit;
    }

    if ($fileSize > $maxSize) {
        http_response_code(422);
        echo json_encode(array('success' => false, 'error' => 'Fichier trop volumineux. Maximum 5 MB.'));
        exit;
    }

    $extension  = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $uniqueName = uniqid('post_', true) . '.' . $extension;
    $uploadDir  = __DIR__ . '/../../uploads/posts/';

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $destination = $uploadDir . $uniqueName;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $destination)) {
        http_response_code(500);
        echo json_encode(array('success' => false, 'error' => "Erreur lors de l'upload du fichier."));
        exit;
    }

    $filePath = '/uploads/posts/' . $uniqueName;
}

// Insertion en base de données
try {
    $stmt = $db->prepare("
        INSERT INTO posts (user_id, content, file_path, created_at)
        VALUES (:user_id, :content, :file_path, NOW())
    ");

    $stmt->execute(array(
        ':user_id'   => $userId,
        ':content'   => $content,
        ':file_path' => $filePath,
    ));

    $newPostId = $db->lastInsertId();

    http_response_code(201);
    echo json_encode(array(
        'success' => true,
        'message' => 'Post créé avec succès.',
        'post_id' => $newPostId,
        'remaining_posts_this_hour' => $rateLimiter->getRemainingPosts($userId)
    ));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur lors de la création du post.'));
}
