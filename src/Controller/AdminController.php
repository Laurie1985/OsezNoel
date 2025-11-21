<?php
namespace App\Controller;

use App\Config\Database;
use App\Model\Calendar;
use App\Model\Statistics;
use App\Model\Surprise;
use App\Model\Theme;
use App\Model\User;

class AdminController extends BaseController
{
    private User $userModel;
    private Calendar $calendarModel;
    private Statistics $statisticsModel;
    private Theme $themeModel;
    private Surprise $surpriseModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel       = new User();
        $this->calendarModel   = new Calendar();
        $this->statisticsModel = new Statistics();
        $this->themeModel      = new Theme();
        $this->surpriseModel   = new Surprise();
    }

    /**
     * Dashboard admin avec statistiques globales
     */
    public function dashboard(): void
    {
        $this->requireAdmin();

        // Statistiques globales
        $totalUsers     = $this->userModel->count();
        $totalCalendars = $this->calendarModel->count();

        // Compter le nombre total d'ouvertures
        $allStats      = $this->statisticsModel->findAll();
        $totalOpenings = count($allStats);

        // Récupérer les dernières ouvertures
        $recentOpenings = $this->statisticsModel->getRecentOpenings(20);

        // Calendriers les plus populaires
        $popularCalendars = $this->getPopularCalendars(5);

        $this->render('admin/dashboard', [
            'title'            => 'Osez Noël - Administration',
            'cssFile'          => 'admin',
            'totalUsers'       => $totalUsers,
            'totalCalendars'   => $totalCalendars,
            'totalOpenings'    => $totalOpenings,
            'recentOpenings'   => $recentOpenings,
            'popularCalendars' => $popularCalendars,
        ]);
    }

    /**
     * Liste de tous les utilisateurs
     */
    public function users(): void
    {
        $this->requireAdmin();

        // Récupérer tous les utilisateurs
        $users = $this->userModel->findAll();

        // Pour chaque utilisateur, compter ses calendriers
        foreach ($users as &$user) {
            $user['calendars_count'] = $this->calendarModel->countByUserId($user['user_id']);
        }

        $this->render('admin/users', [
            'title'      => 'Osez Noël - Gestion des utilisateurs',
            'cssFile'    => 'admin',
            'users'      => $users,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Bloquer/débloquer un utilisateur
     */
    public function toggleBlock(int $userId): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/admin/users');
            return;
        }

        // Vérifier que l'utilisateur existe
        $user = $this->userModel->findById($userId);

        if (! $user) {
            $this->flash('error', 'Utilisateur introuvable.');
            $this->redirect('/admin/users');
            return;
        }

        // Ne pas permettre de bloquer son propre compte
        if ($userId === $_SESSION['user_id']) {
            $this->flash('error', 'Vous ne pouvez pas bloquer votre propre compte.');
            $this->redirect('/admin/users');
            return;
        }

        try {
            // Inverser le statut is_blocked
            $newStatus = ! $user['is_blocked'];

            $this->userModel->update($userId, [
                'is_blocked' => $newStatus,
            ]);

            $message = $newStatus
                ? 'Utilisateur bloqué avec succès.'
                : 'Utilisateur débloqué avec succès.';

            $this->flash('success', $message);
            $this->redirect('/admin/users');

        } catch (\Exception $e) {
            error_log("Erreur toggle block: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de l\'opération. Veuillez réessayer.');
            $this->redirect('/admin/users');
        }
    }

    /**
     * Supprimer un utilisateur
     */
    public function deleteUser(int $userId): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/admin/users');
            return;
        }

        // Vérifier que l'utilisateur existe
        $user = $this->userModel->findById($userId);

        if (! $user) {
            $this->flash('error', 'Utilisateur introuvable.');
            $this->redirect('/admin/users');
            return;
        }

        // Ne pas permettre de supprimer son propre compte
        if ($userId === $_SESSION['user_id']) {
            $this->flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            $this->redirect('/admin/users');
            return;
        }

        try {
            // Récupérer tous les calendriers de l'utilisateur
            $calendars = $this->calendarModel->findByUserId($userId);

            // Supprimer toutes les surprises MongoDB de chaque calendrier
            foreach ($calendars as $calendar) {
                $this->surpriseModel->deleteByCalendarId($calendar['unique_id']);
            }

            // Supprimer l'utilisateur
            // Les calendriers et statistiques seront supprimés automatiquement (CASCADE)
            $this->userModel->delete($userId);

            $this->flash('success', 'Utilisateur et toutes ses données supprimés avec succès.');
            $this->redirect('/admin/users');

        } catch (\Exception $e) {
            error_log("Erreur suppression utilisateur: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la suppression. Veuillez réessayer.');
            $this->redirect('/admin/users');
        }
    }

    /**
     * Liste des thèmes
     */
    public function themes(): void
    {
        $this->requireAdmin();

        // Récupérer tous les thèmes
        $themes = $this->themeModel->findAll();

        // Pour chaque thème, compter combien de calendriers l'utilisent
        foreach ($themes as &$theme) {
            $theme['usage_count'] = $this->calendarModel->countByThemeId($theme['theme_id']);
        }

        $this->render('admin/themes', [
            'title'      => 'Osez Noël - Gestion des thèmes',
            'cssFile'    => 'admin',
            'themes'     => $themes,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Formulaire d'ajout de thème
     */
    public function createTheme(): void
    {
        $this->requireAdmin();

        $this->render('admin/theme-create', [
            'title'      => 'Osez noël - Ajouter un thème',
            'cssFile'    => 'admin',
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Enregistrer un nouveau thème
     */
    public function storeTheme(): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/admin/themes/create');
            return;
        }

        // Récupérer les données
        $themeName = $this->sanitize($_POST['theme_name'] ?? '');

        // Validation
        if (empty($themeName)) {
            $this->flash('error', 'Le nom du thème est obligatoire.');
            $this->redirect('/admin/themes/create');
            return;
        }

        // Gérer l'upload de l'image
        if (! isset($_FILES['theme_image']) || $_FILES['theme_image']['error'] !== UPLOAD_ERR_OK) {
            $this->flash('error', 'Veuillez sélectionner une image.');
            $this->redirect('/admin/themes/create');
            return;
        }

        $file = $_FILES['theme_image'];

        // Validation du type
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        if (! in_array($file['type'], $allowedTypes)) {
            $this->flash('error', 'Format d\'image non autorisé. Utilisez JPG, PNG.');
            $this->redirect('/admin/themes/create');
            return;
        }

        // Validation de la taille (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $this->flash('error', 'L\'image est trop volumineuse (max 5MB).');
            $this->redirect('/admin/themes/create');
            return;
        }

        try {
            // Générer un nom unique pour l'image
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename  = 'theme_' . uniqid() . '.' . $extension;

            // Dossier de destination
            $uploadDir = __DIR__ . '/../../public/assets/images/themes/';

            // Créer le dossier s'il n'existe pas
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $destination = $uploadDir . $filename;

            // Déplacer le fichier uploadé
            if (! move_uploaded_file($file['tmp_name'], $destination)) {
                throw new \Exception('Erreur lors de l\'upload de l\'image.');
            }

            // Créer le thème en base de données
            $this->themeModel->create([
                'theme_name' => $themeName,
                'image_path' => $filename,
            ]);

            $this->flash('success', 'Thème créé avec succès !');
            $this->redirect('/admin/themes');

        } catch (\Exception $e) {
            error_log("Erreur création thème: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la création du thème. Veuillez réessayer.');
            $this->redirect('/admin/themes/create');
        }
    }

    /**
     * Supprimer un thème
     */
    public function deleteTheme(int $themeId): void
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/admin/themes');
            return;
        }

        // Vérifier que le thème existe
        $theme = $this->themeModel->findById($themeId);

        if (! $theme) {
            $this->flash('error', 'Thème introuvable.');
            $this->redirect('/admin/themes');
            return;
        }

        // Vérifier qu'aucun calendrier n'utilise ce thème
        $usageCount = $this->calendarModel->countByThemeId($themeId);

        if ($usageCount > 0) {
            $this->flash('error', 'Impossible de supprimer ce thème : ' . $usageCount . ' calendrier(s) l\'utilisent encore.');
            $this->redirect('/admin/themes');
            return;
        }

        try {
            // Supprimer le fichier image
            $imagePath = __DIR__ . '/../../public/assets/images/themes/' . $theme['image_path'];
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }

            // Supprimer le thème de la base
            $this->themeModel->delete($themeId);

            $this->flash('success', 'Thème supprimé avec succès.');
            $this->redirect('/admin/themes');

        } catch (\Exception $e) {
            error_log("Erreur suppression thème: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la suppression. Veuillez réessayer.');
            $this->redirect('/admin/themes');
        }
    }

    /**
     * Récupérer les calendriers les plus populaires (privé)
     */
    private function getPopularCalendars(int $limit = 5): array
    {
        $db = Database::getConnection();

        $stmt = $db->prepare("
            SELECT
                c.calendar_id,
                c.title,
                u.first_name,
                u.last_name,
                COUNT(s.stat_id) as openings_count
            FROM calendars c
            LEFT JOIN statistics s ON c.calendar_id = s.calendar_id
            LEFT JOIN users u ON c.user_id = u.user_id
            GROUP BY c.calendar_id
            ORDER BY openings_count DESC
            LIMIT ?
        ");
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}
