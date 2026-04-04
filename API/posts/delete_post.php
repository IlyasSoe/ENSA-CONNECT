<?php
/**
 * DELETE /api/posts/{id}
 * Supprime un post — uniquement si owner ou Admin.
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'DELETE' && $method !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Méthode non autorisée.'));
    exit;
}

require_once __DIR__ . '/../../config/Database.php';

try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}

// Récupérer l'ID du post depuis l'URL (?id=42)
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($postId === 0) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'ID du post manquant.'));
    exit;
}

// Récupérer l'utilisateur depuis le body
$body   = json_decode(file_get_contents('php://input'), true);
$userId = isset($body['user_id']) ? (int)$body['user_id'] : 0;

if ($userId === 0 && isset($_POST['user_id'])) {
    $userId = (int)$_POST['user_id'];
}

if ($userId === 0) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'error' => 'Utilisateur non authentifié.'));
    exit;
}

// Récupérer le post
$stmt = $db->prepare("SELECT id, user_id, file_path FROM posts WHERE id = :post_id");
$stmt->execute(array(':post_id' => $postId));
$post = $stmt->fetch();

if (!$post) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'error' => 'Post introuvable.'));
    exit;
}

// Vérifier si Admin
$adminStmt = $db->prepare("SELECT role FROM users WHERE id = :user_id");
$adminStmt->execute(array(':user_id' => $userId));
$user    = $adminStmt->fetch();
$isAdmin = ($user && $user['role'] === 'admin');

// Vérifier permissions
$isOwner = ($post['user_id'] === $userId);

if (!$isOwner && !$isAdmin) {
    http_response_code(403);
    echo json_encode(array('success' => false, 'error' => "Permission refusée. Vous n'êtes pas l'auteur de ce post."));
    exit;
}

// Supprimer le fichier physique si existant
if ($post['file_path']) {
    $physicalPath = __DIR__ . '/../../' . ltrim($post['file_path'], '/');
    if (file_exists($physicalPath)) {
        unlink($physicalPath);
    }
}

// Supprimer en BDD
try {
    $deleteStmt = $db->prepare("DELETE FROM posts WHERE id = :post_id");
    $deleteStmt->execute(array(':post_id' => $postId));

    http_response_code(200);
    echo json_encode(array(
        'success'    => true,
        'message'    => 'Post supprimé avec succès.',
        'deleted_by' => ($isAdmin && !$isOwner) ? 'admin' : 'owner'
    ));

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur lors de la suppression du post.'));
}
