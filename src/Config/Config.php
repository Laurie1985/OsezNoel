<?php
namespace App\Config;

use Dotenv\Dotenv;

class Config
{
    /**Classe pour charger le fichier .env, le lire, séparer et nettoyer les données
    * et fournir une méthode statique pour accéder aux variables d'environnement
    */

    /**
     * @param string $path le chemin vers le dossier contenant le fichier .env
     */

    public static function load($path = null): void
    {
        // Si aucun chemin fourni, utilise le dossier racine du projet
        if ($path === null) {
            // Depuis src/Config/, on remonte de 2 niveaux pour atteindre la racine
            $path = dirname(__DIR__, 2);
        }
        //On vérifie si le fichier .env existe avant de tenter de le charger
        $envFile = $path . '/.env';

        if (file_exists($envFile)) {
            try {
                $dotenv = Dotenv::createImmutable($path);
                $dotenv->load();
            } catch (\Exception $e) {
                error_log("Erreur chargement .env" . $e->getMessage());
            }
        }
    }
    /**
     * @param string $key le nom de la variable d'environnement
     * @param mixed $default la valeur par défaut si la variable n'est pas définie
     * @return mixed la valeur de la variable d'environnement ou la valeur par défaut
     */

    public static function get(string $key, $default = null)
    {
        return $_ENV[$key] ?? $default;
    }
}
