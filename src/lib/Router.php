<?php
namespace App\lib;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use function FastRoute\simpleDispatcher;

class Router
{
    private Dispatcher $dispatcher;

    public function __construct()
    {
        // Initialisation du dispatcher FastRoute
        $this->dispatcher = simpleDispatcher(function (RouteCollector $r) {
            require_once __DIR__ . '/../Config/routes.php';
        });
    }

    /**
     * Dispatch la requête HTTP vers le bon controller et méthode
     */
    public function dispatch()
    {
        // Récupération de l'URL depuis .htaccess
        $url = $_GET['url'] ?? '/';

        // Nettoyage de l'URL
        $url = '/' . trim($url, '/');
        if ($url !== '/') {
            $url = rtrim($url, '/');
        }

        $method = $_SERVER['REQUEST_METHOD'];

        // Dispatch avec FastRoute
        $routeInfo = $this->dispatcher->dispatch($method, $url);

        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND: //Si la route n'est pas trouvée
                $this->handleNotFound();    // Affichage d'une page 404
                break;

            case Dispatcher::METHOD_NOT_ALLOWED:          //Si la méthode HTTP n'est pas autorisée pour cette route
                $this->handleMethodNotAllowed($routeInfo[1]); // Affichage d'une erreur 405 avec les méthodes autorisées
                break;

            case Dispatcher::FOUND:                   //Si la route est trouvée
                $handler = $routeInfo[1];                 // Récupération du contrôleur et de la méthode
                $params  = $routeInfo[2];                 // Récupération des paramètres URL de la route
                $this->callController($handler, $params); // Appel du contrôleur avec les paramètres
                break;
        }
    }

    private function callController($handler, $params)
    {
        list($controllerClass, $method) = $handler; // Extraction du contrôleur et de la méthode

        // Vérification que la classe du contrôleur existe
        if (! class_exists($controllerClass)) {
            throw new \Exception("Classe du controlleur non trouvée : {$controllerClass}");
        }

        // Instanciation du contrôleur
        $controller = new $controllerClass();

        // Vérification que la méthode existe dans le contrôleur
        if (! method_exists($controller, $method)) {
            throw new \Exception("Méthode {$method} non trouvée dans {$controllerClass}");
        }

        // Appel de la méthode avec les paramètres
        call_user_func_array([$controller, $method], $params);
    }

    // Gestion des erreurs
    private function handleNotFound()
    {
        http_response_code(404);
        echo "404 - Page non trouvée";
        // Si tempss, créer une vue 404 personnalisée
        // require VIEW_PATH . '/errors/404.php';
    }

    private function handleMethodNotAllowed($allowedMethods)
    {
        http_response_code(405);
        echo "405 - Méthode non autorisée. Méthodes autorisées : " . implode(', ', $allowedMethods);
    }
}
