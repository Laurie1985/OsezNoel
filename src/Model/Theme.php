<?php
namespace App\Model;

class Theme extends BaseModel
{
    protected string $table      = 'themes';
    protected string $primaryKey = 'theme_id';

    /**
     * Trouver un theme par son nom
     */

    public function findByName(string $name): array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE theme_name = ?");
        $stmt->execute([$name]);
        return $stmt->fetch();
    }
}
