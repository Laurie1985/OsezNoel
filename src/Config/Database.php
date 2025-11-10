<?php
namespace App\Config;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $instance = null;

    /**
     * Connexion MySQL avec PDO (Singleton)
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            try {
                $host     = Config::get('DB_HOST');
                $dbname   = Config::get('DB_NAME');
                $username = Config::get('DB_USER');
                $password = Config::get('DB_PASSWORD');
                $port     = Config::get('DB_PORT', 3306);

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                self::$instance = new PDO($dsn, $username, $password, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);

            } catch (PDOException $e) {
                error_log("Erreur connexion MySQL : " . $e->getMessage());
                die("Erreur de connexion à la base de données.");
            }
        }

        return self::$instance;
    }
}
