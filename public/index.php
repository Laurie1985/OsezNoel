<?php

//Charger la configuration de l'application
require_once __DIR__ . '/../src/Config/bootstrap.php';

use App\lib\Router;

//Création et dispatch du routeur
$router = new Router();
$router->dispatch();
