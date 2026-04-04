<?php
/**
 * DELETE /api/posts/delete_post.php?id=42
 * Supprime un post — uniquement si l'utilisateur est l'auteur ou un Admin.
 *
 * L'utilisateur connecté est récupéré depuis $_SESSION['user_id']
 * (session créée par login.php de khaoulalaanait-coder)
 *
 * Permissions :
 *   - Auteur du post (author_id = user connecté)
 *   - Admin          (role_id = 4 selon schéma Norhane)
 *
 * Schéma utilisé (Norhane) :
 *   posts : id, author_id, content, image_url, TYPE, created_at
 *   users : id, role_id
 */
 
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: DELETE, POST');
 
// ── Vérifier la méthode HTTP ───────────────────────────────────────────────────
$method = $_SERVER['REQUEST_METHOD'];
if ($method !== 'DELETE' && $method !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Méthode non autorisée. Utilisez DELETE.'));
    exit;
}
 
require_once __DIR__ . '/../../config/Database.php';
 
// ── Connexion BDD ──────────────────────────────────────────────────────────────
try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}
 
// ── Vérifier la session (utilisateur connecté) ────────────────────────────────
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'error' => 'Vous devez être connecté.'));
    exit;
}
 
$userId = (int) $_SESSION['user_id'];
 
// ── Récupérer l'ID du post depuis l'URL (?id=42) ──────────────────────────────
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
 
if ($postId === 0) {
    http_response_code(400);
    echo json_encode(array('success' => false, 'error' => 'ID du post manquant ou invalide.'));
    exit;
}
 
// ── Récupérer le post en BDD ───────────────────────────────────────────────────
$stmt = $db->prepare("SELECT id, author_id, image_url FROM posts WHERE id = :post_id");
$stmt->execute(array(':post_id' => $postId));
$post = $stmt->fetch();
 
if (!$post) {
    http_response_code(404);
    echo json_encode(array('success' => false, 'error' => 'Post introuvable.'));
    exit;
}
 
// ── Vérifier si l'utilisateur est Admin (role_id = 4) ─────────────────────────
$adminStmt = $db->prepare("SELECT role_id FROM users WHERE id = :user_id");
$adminStmt->execute(array(':user_id' => $userId));
$user    = $adminStmt->fetch();
$isAdmin = ($user && (int)$user['role_id'] === 4);
 
// ── Vérifier les permissions ───────────────────────────────────────────────────
$isOwner = ((int)$post['author_id'] === $userId);
 
if (!$isOwner && !$isAdmin) {
    http_response_code(403);
    echo json_encode(array(
        'success' => false,
        'error'   => 'Permission refusée. Vous devez être l\'auteur ou un administrateur.'
    ));
    exit;
}
 
// ── Supprimer en BDD ───────────────────────────────────────────────────────────
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
