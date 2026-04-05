<?php
/**
 * POST /api/posts
 * Crée un nouveau post (texte + image optionnelle).
 *
 * Paramètres POST attendus :
 *   - content   (string, obligatoire) : texte du post
 *   - image_url (string, optionnel)   : URL de l'image
 *   - TYPE      (string, optionnel)   : 'status' ou 'offer' (défaut: 'status')
 *
 * L'utilisateur connecté est récupéré depuis $_SESSION['user_id']
 * (session créée par login.php de khaoulalaanait-coder)
 *
 * Schéma utilisé (Norhane) :
 *   posts : id, author_id, content, image_url, TYPE, created_at
 */
 
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
 
// ── Vérifier la méthode HTTP ───────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(array('success' => false, 'error' => 'Méthode non autorisée. Utilisez POST.'));
    exit;
}
 
require_once __DIR__ . '/../../config/Database.php';
require_once __DIR__ . '/../../middleware/XSSProtection.php';
require_once __DIR__ . '/../../middleware/RateLimiter.php';
 
// ── Connexion BDD ──────────────────────────────────────────────────────────────
try {
    $db = Database::connect();
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur de connexion à la base de données.'));
    exit;
}
 
// ── Vérifier la session (utilisateur connecté) ─────────────────────────────────
// $_SESSION['user_id'] est défini par login.php de khaoulalaanait-coder
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(array('success' => false, 'error' => 'Vous devez être connecté pour poster.'));
    exit;
}
 
$userId = (int) $_SESSION['user_id'];
 
// ── Rate Limiting : max 10 posts/heure ────────────────────────────────────────
$rateLimiter = new RateLimiter($db);
 
if (!$rateLimiter->isAllowed($userId)) {
    http_response_code(429);
    echo json_encode(array(
        'success'     => false,
        'error'       => 'Limite atteinte : maximum 10 posts par heure.',
        'retry_after' => '1 heure'
    ));
    exit;
}
 
// ── Validation & nettoyage XSS du contenu ─────────────────────────────────────
$rawContent = isset($_POST['content']) ? $_POST['content'] : '';
$validation = XSSProtection::validatePostContent($rawContent);
 
if (!$validation['valid']) {
    http_response_code(422);
    echo json_encode(array('success' => false, 'error' => $validation['error']));
    exit;
}
 
$content = $validation['content'];
 
// ── Récupérer et valider les autres champs ────────────────────────────────────
// image_url : URL fournie par l'utilisateur (optionnel)
$imageUrl = isset($_POST['image_url']) ? XSSProtection::sanitize($_POST['image_url']) : null;
if (empty($imageUrl)) {
    $imageUrl = null;
}
 
// TYPE : 'status' ou 'offer' selon le schéma de Norhane
$allowedTypes = array('status', 'offer');
$postType = isset($_POST['TYPE']) && in_array($_POST['TYPE'], $allowedTypes)
    ? $_POST['TYPE']
    : 'status';
 
// ── Insertion en base de données ───────────────────────────────────────────────
try {
    $stmt = $db->prepare("
        INSERT INTO posts (author_id, content, image_url, TYPE, created_at)
        VALUES (:author_id, :content, :image_url, :type, NOW())
    ");
 
    $stmt->execute(array(
        ':author_id' => $userId,
        ':content'   => $content,
        ':image_url' => $imageUrl,
        ':type'      => $postType,
    ));
 
    $newPostId = (int) $db->lastInsertId();
 
    http_response_code(201);
    echo json_encode(array(
        'success'                    => true,
        'message'                    => 'Post créé avec succès.',
        'post_id'                    => $newPostId,
        'remaining_posts_this_hour'  => $rateLimiter->getRemainingPosts($userId)
    ));
 
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(array('success' => false, 'error' => 'Erreur lors de la création du post.'));
}
