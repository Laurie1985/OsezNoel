<?php
namespace App\Config;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\Database as MongoDatabase;

class MongoDB
{
    private static ?MongoDatabase $instance = null;

    /**
     * Connexion MongoDB (Singleton)
     */
    public static function getConnection(): MongoDatabase
    {
        if (self::$instance === null) {
            try {
                $host     = Config::get('MONGO_HOST');
                $port     = Config::get('MONGO_PORT', 27017);
                $username = Config::get('MONGO_INITDB_ROOT_USERNAME');
                $password = Config::get('MONGO_INITDB_ROOT_PASSWORD');
                $database = Config::get('MONGO_DATABASE');

                $uri = "mongodb://{$username}:{$password}@{$host}:{$port}";

                $client         = new Client($uri);
                self::$instance = $client->selectDatabase($database);

            } catch (\Exception $e) {
                error_log("Erreur connexion MongoDB : " . $e->getMessage());
                die("Erreur de connexion à MongoDB.");
            }
        }

        return self::$instance;
    }

    /**
     * Retourne une collection MongoDB
     *
     * @param string $collectionName Nom de la collection
     * @return Collection
     */
    public static function getCollection(string $collectionName): Collection
    {
        $db = self::getConnection();
        return $db->selectCollection($collectionName);
    }
}
