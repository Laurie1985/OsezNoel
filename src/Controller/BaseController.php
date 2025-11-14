<?php
namespace App\Controller;

use App\Config\Database;
use App\Security\TokenManager;

// Classe abstraite de base pour les contrôleurs
abstract class BaseController
{
    protected $db;
    protected $tokenManager;

    public function __construct()
    {
        $this->db           = Database::getConnection();
        $this->tokenManager = new TokenManager();
    }
    // Charge et affiche une vue
    public function render(string $view, array $data = [], ?string $layout = 'base'): void
    {
        // Extraction du contenu des données
        extract($data);

        //Ajouter les messages flash s'il y en a
        $flashMessages = $this->getFlashMessages();

        $viewPath = VIEW_PATH . '/' . $view . '.php';

        if (! file_exists($viewPath)) {
            throw new \Exception("La vue {$view} n'existe pas.");
        }

        //Si pas de layout
        if ($layout === null) {
            require $viewPath;
            return;
        }

        //Charger la vue
        ob_start();
        require $viewPath;
        $content = ob_get_clean();

        //Charger le layout
        $layoutPath = VIEW_PATH . '/layouts/' . $layout . '.php';

        if (! file_exists($layoutPath)) {
            throw new \Exception("Le layout {$layout} n'existe pas.");
        }

        require $layoutPath;
    }
    /**
     * Génère un token CSRF
     */
    protected function generateCsrfToken()
    {
        return $this->tokenManager->generateCsrfToken();
    }
    /**
     * Vérifie le token CSRF
     */
    protected function requireCsrfToken(): bool
    {
        if (! $this->tokenManager->checkCsrfToken($_POST['csrf_token'] ?? '')) {
            $this->flash('error', 'Token de sécurité invalide');
            return false;
        }
        return true;
    }

    /**
     * Redirection vers une URL
     */
    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    /**
     * Redirige vers la page précédente
     */
    protected function redirectBack(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    /**
     * Retourne une réponse JSON
     */
    protected function json($data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Ajoute un message flash
     */
    protected function flash(string $type, string $message): void
    {
        if (! isset($_SESSION['flash_messages'])) {
            $_SESSION['flash_messages'] = [];
        }

        $_SESSION['flash_messages'][] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    /**
     * Récupère et supprime les messages flash
     */
    protected function getFlashMessages(): array
    {
        $messages = $_SESSION['flash_messages'] ?? [];
        unset($_SESSION['flash_messages']);
        return $messages;
    }

    /**
     * Vérifie si l'utilisateur est connecté
     */
    protected function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Récupère l'utilisateur connecté
     */
    protected function getAuthUser(): ?array
    {
        if (! $this->isAuthenticated()) {
            return null;
        }

        return $_SESSION['user'] ?? null;
    }
    /**
     * Vérifie si l'utilisateur est admin
     */
    protected function isAdmin(): bool
    {
        $user = $this->getAuthUser();
        return $user && ($user['is_admin'] ?? false);
    }

    /**
     * Exige que l'utilisateur soit connecté
     */
    protected function requireAuth(): void
    {
        if (! $this->isAuthenticated()) {
            $this->flash('error', 'Vous devez être connecté pour accéder à cette page.');
            $this->redirect('/login');
        }
    }

    /**
     * Exige que l'utilisateur soit admin
     */
    protected function requireAdmin(): void
    {
        $this->requireAuth();

        if (! $this->isAdmin()) {
            $this->flash('error', 'Accès interdit. Vous devez être administrateur.');
            $this->redirect('/');
        }
    }

    protected function sanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'sanitize'], $data);
        }

        return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
    }
    /**
     * Valide les données d'un formulaire
     */
    protected function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? null;

            if (str_contains($rule, 'required') && empty($value)) {
                $errors[$field] = "Le champ {$field} est obligatoire.";
                continue;
            }

            if (str_contains($rule, 'email') && ! empty($value) && ! filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "Le champ {$field} doit être un email valide.";
            }

            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $min = (int) $matches[1];
                if (! empty($value) && strlen($value) < $min) {
                    $errors[$field] = "Le champ {$field} doit contenir au moins {$min} caractères.";
                }
            }

            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $max = (int) $matches[1];
                if (! empty($value) && strlen($value) > $max) {
                    $errors[$field] = "Le champ {$field} doit contenir au maximum {$max} caractères.";
                }
            }
        }

        return $errors;
    }
}
