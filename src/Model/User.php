<?php
namespace App\Model;

class User extends BaseModel
{
    // Le nom de la table
    protected string $table = 'users';
    // La clé primaire de la table
    protected string $primaryKey = 'user_id';

    /**
     * Trouve un utilisateur par son email
     */

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE email = ?");
        $stmt->execute([$email]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
    /**
     * Bloque ou débloque un utilisateur
     */
    public function userBlocked(int $id): bool
    {
        $stmt = $this->db->prepare("
            UPDATE {$this->table}
            SET is_blocked = NOT is_blocked
            WHERE {$this->primaryKey} = ?
        ");

        return $stmt->execute([$id]);
    }
    /**
     * Recherche des utilisateurs par nom ou email
     */
    public function searchUsers(string $query): array
    {
        $stmt       = $this->db->prepare("SELECT * FROM {$this->table} WHERE last_name LIKE ? OR first_name LIKE ? OR email LIKE ? ORDER BY created_at DESC");
        $searchTerm = "%{$query}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);

        return $stmt->fetchAll();
    }

    /**
     * Compte le nombre d'utilisateurs actifs (non bloqués)
     */
    public function countActiveUsers(): int
    {
        $stmt   = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE is_blocked = 0");
        $result = $stmt->fetch();
        return (int) $result['count'];
    }

    /**
     * Récupère les derniers utilisateurs inscrits
     */

    public function getLatestUsers(int $limit = 5): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} ORDER BY created_at DESC LIMIT ?");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
