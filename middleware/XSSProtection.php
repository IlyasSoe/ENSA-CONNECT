<?php
/**
 * XSSProtection.php
 * Nettoie les données contre les attaques XSS.
 */
class XSSProtection
{
    public static function sanitize($data)
    {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
        return $data;
    }

    public static function sanitizeArray($dataArray)
    {
        $cleaned = array();
        foreach ($dataArray as $key => $value) {
            if (is_array($value)) {
                $cleaned[$key] = self::sanitizeArray($value);
            } else {
                $cleaned[$key] = self::sanitize((string)$value);
            }
        }
        return $cleaned;
    }

    public static function validatePostContent($content)
    {
        $content = self::sanitize($content);

        if (empty($content)) {
            return array('valid' => false, 'content' => '', 'error' => 'Le contenu ne peut pas être vide.');
        }

        if (strlen($content) > 5000) {
            return array('valid' => false, 'content' => '', 'error' => 'Le contenu ne peut pas dépasser 5000 caractères.');
        }

        return array('valid' => true, 'content' => $content, 'error' => '');
    }
}
