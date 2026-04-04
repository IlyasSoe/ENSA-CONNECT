<?php
/**
 * GET /api/posts
 * Récupère les posts avec pagination (?page=X)
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../../config/Database.php';

try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}

$page    = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$totalStmt  = $db->query("SELECT COUNT(*) FROM posts");
$totalPosts = (int) $totalStmt->fetchColumn();
$totalPages = (int) ceil($totalPosts / $perPage);

$stmt = $db->prepare("
    SELECT 
        p.id,
        p.content,
        p.file_path,
        p.created_at,
        u.id       AS user_id,
        u.username AS author
    FROM posts p
    JOIN users u ON p.user_id = u.id
    ORDER BY p.created_at DESC
    LIMIT :limit OFFSET :offset
");

$stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

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
