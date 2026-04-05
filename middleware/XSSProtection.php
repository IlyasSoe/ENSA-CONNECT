<?php
/**
 * RateLimiter.php
 * Limite chaque utilisateur à 10 posts maximum par heure.
 *
 * Comment ça marche ?
 * On compte les posts de l'utilisateur dans la dernière heure.
 * Si le nombre atteint 10 → on bloque et on retourne une erreur 429.
 *
 * Colonne utilisée : author_id (selon le schéma de Norhane)
 */
class RateLimiter
{
    private $db;
    private $maxRequests = 10;   // Max 10 posts
    private $timeWindow  = 3600; // Par heure (3600 secondes)
 
    public function __construct($db)
    {
        $this->db = $db;
    }
 
    /**
     * Vérifie si l'utilisateur peut encore poster
     * @return bool true = autorisé, false = bloqué
     */
    public function isAllowed($userId)
    {
        return $this->getRecentPostCount($userId) < $this->maxRequests;
    }
 
    /**
     * Compte les posts de l'utilisateur dans la dernière heure
     */
    public function getRecentPostCount($userId)
    {
        $oneHourAgo = date('Y-m-d H:i:s', time() - $this->timeWindow);
 
        $stmt = $this->db->prepare("
            SELECT COUNT(*)
            FROM posts
            WHERE author_id = :author_id
              AND created_at >= :one_hour_ago
        ");
 
        $stmt->execute(array(
            ':author_id'   => $userId,
            ':one_hour_ago' => $oneHourAgo
        ));
 
        return (int) $stmt->fetchColumn();
    }
 
    /**
     * Retourne le nombre de posts restants pour cette heure
     */
    public function getRemainingPosts($userId)
    {
        return max(0, $this->maxRequests - $this->getRecentPostCount($userId));
    }
}
