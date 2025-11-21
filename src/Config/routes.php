<?php

/**
 * Définition des routes de l'application
 *
 * $r est une instance de FastRoute\RouteCollector
 */

use App\Controller\AdminController;
use App\Controller\AuthController;
use App\Controller\CalendarController;
use App\Controller\HomeController;
use App\Controller\ShareController;
use App\Controller\SurpriseController;

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

// Mettre à jour une surprise
$r->post('/calendars/{id:\d+}/surprises/{day:\d+}', [SurpriseController::class, 'update']);

// Supprimer une surprise
$r->post('/calendars/{id:\d+}/surprises/{day:\d+}/delete', [SurpriseController::class, 'delete']);

// Afficher le calendrier partagé
$r->get('/share/{token}', [ShareController::class, 'show']);

// Ouvrir une case
$r->get('/share/{token}/open/{day:\d+}', [ShareController::class, 'open']);

// Dashboard admin
$r->get('/admin/dashboard', [AdminController::class, 'dashboard']);
$r->get('/admin', [AdminController::class, 'dashboard']); // Alias

// Gestion des utilisateurs
$r->get('/admin/users', [AdminController::class, 'users']);
$r->post('/admin/users/{id:\d+}/toggle-block', [AdminController::class, 'toggleBlock']);
$r->post('/admin/users/{id:\d+}/delete', [AdminController::class, 'deleteUser']);

// Gestion des thèmes
$r->get('/admin/themes', [AdminController::class, 'themes']);
$r->get('/admin/themes/create', [AdminController::class, 'createTheme']);
$r->post('/admin/themes', [AdminController::class, 'storeTheme']);
$r->post('/admin/themes/{id:\d+}/delete', [AdminController::class, 'deleteTheme']);
