<?php
/**
 * GET /api/posts
 * Récupère la liste des posts avec pagination.
 *
 * Utilisation :
 *   GET /api/posts/get_posts.php          → page 1
 *   GET /api/posts/get_posts.php?page=2   → page 2
 *
 * Schéma utilisé (Norhane) :
 *   posts   : id, author_id, content, image_url, TYPE, created_at
 *   users   : id, username, role_id
 */
 
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
 
require_once __DIR__ . '/../../config/Database.php';
 
// ── Connexion BDD ──────────────────────────────────────────────────────────────
try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}
 
// ── Pagination ─────────────────────────────────────────────────────────────────
$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset  = ($page - 1) * $perPage;
 
// ── Total des posts ────────────────────────────────────────────────────────────
$totalStmt  = $db->query("SELECT COUNT(*) FROM posts");
$totalPosts = (int) $totalStmt->fetchColumn();
$totalPages = (int) ceil($totalPosts / $perPage);
 
// ── Récupérer les posts ────────────────────────────────────────────────────────
// On joint avec users pour récupérer username et role_id
$stmt = $db->prepare("
    SELECT
        p.id,
        p.author_id,
        p.content,
        p.image_url,
        p.TYPE,
        p.created_at,
        u.username,
        u.role_id
    FROM posts p
    JOIN users u ON p.author_id = u.id
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");
 
$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();
 
// ── Réponse JSON ───────────────────────────────────────────────────────────────
http_response_code(200);
echo json_encode(array(
    'success' => true,
    'data'    => $posts,
    'pagination' => array(
        'current_page' => $page,
        'total_pages'  => $totalPages,
        'total_posts'  => $totalPosts,
        'per_page'     => $perPage,
    )
));
