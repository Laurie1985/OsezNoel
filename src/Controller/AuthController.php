<?php
namespace App\Controller;

use App\Model\User;

class AuthController extends BaseController
{

    private User $userModel;

    public function __construct()
    {
        parent::__construct(); // ← Important ! Appelle le constructeur de BaseController
        $this->userModel = new User();
    }
    /**
     * Affiche le formulaire de connexion
     */
    public function showLoginForm(): void
    {
        // Si déjà connecté, redirige vers dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/calendars');
            return;
        }

        $this->render('auth/login', [
            'title'      => 'Osez Noël - Connexion',
            'cssFile'    => 'auth',
            'jsFile'     => 'auth',
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Connexion
     */
    public function login(): void
    {
        if (! $this->requireCsrfToken()) {
            $this->redirect('/login');
            return;
        }

        // Vérifie que c'est bien une requête POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/login');
            return;
        }

        $email    = $this->sanitize($_POST['login'] ?? '');
        $password = $_POST['password'] ?? '';

        // Validation des champs
        if (empty($email) || empty($password)) {
            $this->flash('error', 'Veuillez remplir tous les champs');
            $this->redirectBack();
            return;
        }

        // Vérification des identifiants
        try {
            $user = $this->userModel->findByEmail($email);

            // Vérifier que l'utilisateur existe ET que le mot de passe est correct
            if (! $user || ! password_verify($password, $user['password_hash'])) {
                $this->flash('error', 'Email ou mot de passe incorrect.');
                $this->redirectBack();
                return;
            }

            // Vérifier si le compte est bloqué
            if ($user['is_blocked']) {
                $this->flash('error', 'Votre compte a été bloqué. Contactez l\'administrateur.');
                $this->redirectBack();
                return;
            }

            // Créer la session utilisateur
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user']    = [
                'user_id'    => $user['user_id'],
                'email'      => $user['email'],
                'is_admin'   => $user['is_admin'],
                'created_at' => $user['created_at'],
            ];

            $this->flash('success', 'Connexion réussie ! Bienvenue.');
            $this->redirect('/calendars');

        } catch (\Exception $e) {
            error_log("Erreur login: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la connexion. Veuillez réessayer plus tard.');
            $this->redirectBack();
        }
    }

    /**
     * Affiche le formulaire d'inscription
     */
    public function showRegisterForm(): void
    {
        // Si déjà connecté, redirige vers dashboard
        if ($this->isAuthenticated()) {
            $this->redirect('/calendars');
            return;
        }

        $this->render('auth/register', [
            'title'      => 'Osez Noël - Inscription',
            'cssFile'    => 'auth',
            'jsFile'     => 'auth',
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Inscription
     */
    public function register()
    {
        if (! $this->requireCsrfToken()) {
            $this->redirectBack();
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/register');
            return;
        }
        $firstname       = $this->sanitize($_POST['first_name'] ?? '');
        $lastname        = $this->sanitize($_POST['last_name'] ?? '');
        $email           = $this->sanitize($_POST['email'] ?? '');
        $password        = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validation des champs
        $errors = [];

        if (empty($firstname) || empty($lastname) || empty($email) || empty($password)) {
            $errors[] = 'Tous les champs sont obligatoires';
        }

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email invalide';
        }

        // Validation mot de passe sécurisé
        if (strlen($password) < 8) {
            $errors[] = 'Le mot de passe doit contenir au moins 8 caractères';
        }

        if (! preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une majuscule';
        }

        if (! preg_match('/[a-z]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins une minuscule';
        }

        if (! preg_match('/[0-9]/', $password)) {
            $errors[] = 'Le mot de passe doit contenir au moins un chiffre';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Les mots de passe ne correspondent pas';
        }

        if (! empty($errors)) {
            foreach ($errors as $error) {
                $this->flash('error', $error);
            }
            $this->redirectBack();
            return;
        }

        try {
            // Vérifier si email est déjà utilisé
            $existingEmail = $this->userModel->findByEmail($email);
            if ($existingEmail !== null) {
                $this->flash('error', 'Cette adresse email est déjà utilisée');
                $this->redirectBack();
                return;
            }

            // Création du compte utilisateur
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);

            $userId = $this->userModel->create([
                'first_name'    => $firstname,
                'last_name'     => $lastname,
                'email'         => $email,
                'password_hash' => $passwordHash,
            ]);

            //Connexion automatique après insription
            $_SESSION['user_id'] = $userId;
            $_SESSION['user']    = [
                'user_id'    => $userId,
                'email'      => $email,
                'is_admin'   => false,
                'created_at' => date('Y-m-d H:i:s'),
            ];

            $this->flash('success', "Bienvenue {$firstname} ! Votre compte a été créé avec succès.");

            // Redirection
            $this->redirect('/calendars');

        } catch (\Exception $e) {
            error_log("Erreur inscription: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la création du compte');
            $this->redirectBack();
            return;
        }

    }

    /**
     * Déconnexion
     */
    public function logout(): void
    {
        //vider les variables de session
        $_SESSION = [];

        // Supprimer le cookie de session côté client
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }

        session_destroy();                                              //Détruire la session côté serveur
        session_start();                                                //Recréer une nouvelle session
        $this->flash('success', 'Vous êtes maintenant déconnecté.'); //Message de succès
        $this->redirect('/');                                           //Redirection vers la page d'accueil
    }
}
