<?php

/**
 * Définition des routes de l'application
 *
 * $r est une instance de FastRoute\RouteCollector
 */

use App\Controller\AuthController;
use App\Controller\CalendarController;
use App\Controller\HomeController;

// Page d'accueil
$r->get('/', [HomeController::class, 'index']);
$r->get('/home', [HomeController::class, 'index']);

// Afficher le formulaire login/register
$r->get('/login', [AuthController::class, 'showLoginForm']);

// Traitement connexion
$r->post('/login', [AuthController::class, 'login']);

// Traitement inscription
$r->post('/register', [AuthController::class, 'register']);

// Déconnexion
$r->get('/logout', [AuthController::class, 'logout']);

// Liste des calendriers
$r->get('/calendars', [CalendarController::class, 'index']);

// Afficher le formulaire de création
$r->get('/calendars/create', [CalendarController::class, 'create']);

// Enregistrer un nouveau calendrier
$r->post('/calendars', [CalendarController::class, 'store']);

// Afficher un calendrier
$r->get('/calendars/{id:\d+}', [CalendarController::class, 'show']);

// Modifier un calendrier
$r->get('/calendars/{id:\d+}/edit', [CalendarController::class, 'edit']);
$r->post('/calendars/{id:\d+}', [CalendarController::class, 'update']);

// Supprimer un calendrier
$r->post('/calendars/{id:\d+}/delete', [CalendarController::class, 'delete']);
