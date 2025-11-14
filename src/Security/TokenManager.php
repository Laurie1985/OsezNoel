<?php
namespace App\Security;

/**
 * Gère les jetons (tokens) de sécurité
 */

class TokenManager
{
    public function generateCsrfToken(): string
    {
        // Si aucun jeton n'existe en session, on en crée un.
        if (! isset($_SESSION['csrf_token'])) {
            // `random_bytes()` génère une chaîne de caractères aléatoires cryptographiquement sûre.
            // `bin2hex()` la convertit en une chaîne hexadécimale lisible.
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    /**
     * Vérifie si le token CSRF est valide
     */
    public function checkCsrfToken(string $token): bool
    {
        // `hash_equals()` pour comparer les jetons.
        return ! empty($token) && ! empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
