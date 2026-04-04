<?php
/**
 * RateLimiter.php
 * Limite le nombre de posts à 10 par heure par utilisateur.
 */
class RateLimiter
{
    private $db;
    private $maxRequests = 10;
    private $timeWindow  = 3600; // 1 heure en secondes

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function isAllowed($userId)
    {
        $count = $this->getRecentPostCount($userId);
        return $count < $this->maxRequests;
    }

    public function getRecentPostCount($userId)
    {
        $oneHourAgo = date('Y-m-d H:i:s', time() - $this->timeWindow);

        $stmt = $this->db->prepare("
            SELECT COUNT(*) 
            FROM posts 
            WHERE user_id = :user_id 
              AND created_at >= :one_hour_ago
        ");
        $stmt->execute([
            ':user_id'      => $userId,
            ':one_hour_ago' => $oneHourAgo
        ]);

        return (int) $stmt->fetchColumn();
    }

    public function getRemainingPosts($userId)
    {
        $used = $this->getRecentPostCount($userId);
        return max(0, $this->maxRequests - $used);
    }
}
