<?php
namespace App\Model;

class Calendar extends BaseModel
{
    protected string $table      = 'calendars';
    protected string $primaryKey = 'calendar_id';

    /**
     * Récupérer tous les calendriers d'un utilisateur
     */

    public function findByUserId(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE user_id = ? ORDER BY {$this->primaryKey} DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Trouver un calendrier par son token de partage (pour les invités)
     */

    public function findByShareToken(string $token): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE share_token = ?");
        $stmt->execute([$token]);
        $calendar = $stmt->fetch();
        return $calendar ?: null;
    }

    /**
     * Trouver un calendrier par son ID unique
     */

    public function findByUniqueId(string $uniqueId): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE unique_id = ?");
        $stmt->execute([$uniqueId]);
        $calendar = $stmt->fetch();
        return $calendar ?: null;
    }

    /**
     * Générer un ID unique pour un calendrier
     */

    public function generateUniqueId(): string
    {
        do {
            $uniqueId = 'cal_' . bin2hex(random_bytes(8));
            $stmt     = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE unique_id = ?");
            $stmt->execute([$uniqueId]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);
        return $uniqueId;
    }

    /**
     * Générer un token de partage sécurisé
     */

    public function generateShareToken(): string
    {
        do {
            $shareToken = bin2hex(random_bytes(32));
            $stmt       = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE share_token = ?");
            $stmt->execute([$shareToken]);
            $count = $stmt->fetchColumn();
        } while ($count > 0);
        return $shareToken;
    }

    /**
     * Récupérer un calendrier avec les infos du thème
     */

    public function findWithThemeById(int $calendarId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, t.theme_name, t.image_path AS theme_image_path
            FROM {$this->table} c
            JOIN themes t ON c.theme_id = t.theme_id
            WHERE c.{$this->primaryKey} = ?"
        );
        $stmt->execute([$calendarId]);
        $calendar = $stmt->fetch();
        return $calendar ?: null;
    }

    /**
     * Récupérer tous les calendriers d'un utilisateur avec les thèmes
     */

    public function findAllWithThemesByUserId(int $userId): array
    {
        $stmt = $this->db->prepare(
            "SELECT c.*, t.theme_name, t.image_path AS theme_image_path
            FROM {$this->table} c
            JOIN themes t ON c.theme_id = t.theme_id
            WHERE c.user_id = ?
            ORDER BY c.{$this->primaryKey} DESC"
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Compter le nombre de calendriers d'un utilisateur
     */
    public function countByUserId(int $userId): int
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM {$this->table}
        WHERE user_id = ?
    ");
        $stmt->execute([$userId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Compter le nombre de calendriers utilisant un thème
     */
    public function countByThemeId(int $themeId): int
    {
        $stmt = $this->db->prepare("
        SELECT COUNT(*) FROM {$this->table}
        WHERE theme_id = ?
    ");
        $stmt->execute([$themeId]);
        return (int) $stmt->fetchColumn();
    }
}
