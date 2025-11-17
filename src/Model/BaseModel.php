<?php
namespace App\Model;

use App\Config\Database;
use PDO;

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    /**
     * Trouve un enregistrement par son ID
     */
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table}  WHERE  {$this->primaryKey} = ?");
        $stmt->execute([$id]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Récupère tous les enregistrements
     */
    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$this->primaryKey} DESC");
        return $stmt->fetchAll();
    }

    /**
     * Crée un nouvel enregistrement
     */
    public function create(array $data): int
    {
        $columns      = array_keys($data);                // Extrait les clés du tableau associatif
        $placeholders = array_fill(0, count($data), '?'); // Crée un tableau avec la même valeur

        $stmt = $this->db->prepare("INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")");
        $stmt->execute(array_values($data));
        return (int) $this->db->lastInsertId();
    }

    /**
     * Met à jour un enregistrement
     */
    public function update(int $id, array $data): bool
    {
        $updates = [];
        $params  = [];

        foreach ($data as $column => $value) {
            $updates[] = "$column = ?";
            $params[]  = $value;
        }

        $params[] = $id; // ID à la fin pour le WHERE

        $stmt = $this->db->prepare("UPDATE {$this->table} SET " . implode(', ', $updates) . " WHERE {$this->primaryKey} = ?");
        return $stmt->execute($params);
    }

    /**
     * Supprime un enregistrement
     */
    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->table} WHERE {$this->primaryKey} = ?");
        return $stmt->execute([$id]);
    }

    /**
     * Compte le nombre total d'enregistrements
     */
    public function count(): int
    {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM  {$this->table}");
        return (int) $stmt->fetchColumn();
    }
}
