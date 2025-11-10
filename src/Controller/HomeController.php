<?php
namespace App\Controller;

class HomeController
{
    /**
     * Page d'accueil
     */
    public function index(): void
    {
        echo "<h1>🎄 Bienvenue sur Osez Noël !</h1>";
        echo "<p>Créez et partagez vos calendriers de l'Avent personnalisés</p>";
        echo "<ul>";
        echo "<li><a href='/test'>Page de test</a></li>";
        echo "</ul>";
    }

    /**
     * Page de test
     */
    public function test(): void
    {
        echo "<h1>Page de test</h1>";
        echo "<p>Le routeur fonctionne parfaitement ! ✅</p>";
        echo "<p><a href='/'>Retour à l'accueil</a></p>";

        // Test des connexions DB
        echo "<h2>Test des connexions</h2>";

        try {
            $pdo = \App\Config\Database::getConnection();
            echo "<p>✅ MySQL connecté</p>";
        } catch (\Exception $e) {
            echo "<p>❌ MySQL : " . $e->getMessage() . "</p>";
        }

        try {
            $mongo = \App\Config\MongoDB::getConnection();
            echo "<p>✅ MongoDB connecté : " . $mongo->getDatabaseName() . "</p>";
        } catch (\Exception $e) {
            echo "<p>❌ MongoDB : " . $e->getMessage() . "</p>";
        }
    }
}
