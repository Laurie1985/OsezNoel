<?php

/**
 * Définition des routes de l'application
 *
 * $r est une instance de FastRoute\RouteCollector
 */

use App\Controller\HomeController;

// Page d'accueil
$r->get('/', [HomeController::class, 'index']);
$r->get('/home', [HomeController::class, 'index']);

// Route de test
$r->get('/test', [HomeController::class, 'test']);
