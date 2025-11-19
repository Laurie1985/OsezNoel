<?php
namespace App\Model;

class Statistics extends BaseModel
{
    protected string $table      = 'statistics';
    protected string $primaryKey = 'stat_id';

    /**
     * Enregistrer l'ouverture d'une case
     * Retourne true si succès, false si déjà ouverte
     */
    public function recordOpening(int $calendarId, int $day): bool
    {
        try {
            // Essayer d'insérer
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (calendar_id, day) VALUES (?, ?)");

            return $stmt->execute([$calendarId, $day]);

        } catch (\PDOException $e) {
            // Si erreur de UNIQUE constraint, la case était déjà ouverte
            if ($e->getCode() === '23000') {
                return false; // Déjà ouverte
            }
            throw $e; // Autre erreur
        }
    }

    /**
     * Vérifier si une case a été ouverte
     */
    public function isOpened(int $calendarId, int $day): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE calendar_id = ? AND day = ?");
        $stmt->execute([$calendarId, $day]);
        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Récupérer toutes les cases ouvertes d'un calendrier
     */
    public function findByCalendarId(int $calendarId): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE calendar_id = ? ORDER BY day ASC");
        $stmt->execute([$calendarId]);
        return $stmt->fetchAll();
    }

    /**
     * Compter le nombre de cases ouvertes d'un calendrier
     */
    public function countByCalendarId(int $calendarId): int
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM {$this->table} WHERE calendar_id = ?");
        $stmt->execute([$calendarId]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Récupérer les stats d'un calendrier
     */
    public function getCalendarStats(int $calendarId): array
    {
        $opened = $this->countByCalendarId($calendarId);
        $total  = 24;

        return [
            'total'     => $total,
            'opened'    => $opened,
            'remaining' => $total - $opened,
            'progress'  => round(($opened / $total) * 100, 2),
        ];
    }

    /**
     * Récupérer les jours ouverts (pour affichage)
     */
    public function getOpenedDays(int $calendarId): array
    {
        $stmt = $this->db->prepare("SELECT day FROM {$this->table} WHERE calendar_id = ? ORDER BY day ASC");
        $stmt->execute([$calendarId]);

        return array_column($stmt->fetchAll(), 'day');
    }

    /**
     * Récupérer la date d'ouverture d'une case
     */
    public function getOpeningDate(int $calendarId, int $day): ?string
    {
        $stmt = $this->db->prepare("SELECT opened_at FROM {$this->table} WHERE calendar_id = ? AND day = ?");
        $stmt->execute([$calendarId, $day]);

        $result = $stmt->fetchColumn();
        return $result ?: null;
    }

    /**
     * Récupérer les dernières ouvertures (pour dashboard admin)
     */
    public function getRecentOpenings(int $limit = 10): array
    {
        $stmt = $this->db->prepare("
            SELECT s.*, c.title as calendar_title, u.first_name, u.last_name
            FROM {$this->table} s
            JOIN calendars c ON s.calendar_id = c.calendar_id
            JOIN users u ON c.user_id = u.user_id
            ORDER BY s.opened_at DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
