<?php

// Charger l'autoloader de Composer
require_once __DIR__ . '/../../vendor/autoload.php';

use App\Config\Config;

// Charger les variables d'environnement
Config::load();

// Définir les constantes de l'application
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_URL', Config::get('APP_URL', 'http://localhost:8080'));
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('VIEW_PATH', ROOT_PATH . '/View');

// Configuration de l'environnement
$appEnv = Config::get('APP_ENV', 'dev');
ini_set('display_errors', $appEnv === 'dev' ? '1' : '0');
error_reporting($appEnv === 'dev' ? E_ALL : 0);

// Démarrer la session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
