<?php
namespace App\Controller;

use App\Model\Calendar;
use App\Model\Statistics;
use App\Model\Surprise;
use App\Model\Theme;

class CalendarController extends BaseController
{
    private Calendar $calendarModel;
    private Theme $themeModel;
    private Surprise $surpriseModel;
    private Statistics $statisticsModel;

    public function __construct()
    {
        parent::__construct();
        $this->calendarModel   = new Calendar();
        $this->themeModel      = new Theme();
        $this->surpriseModel   = new Surprise();
        $this->statisticsModel = new Statistics();
    }

    /**
     * Liste de mes calendriers
     */
    public function index(): void
    {
        $this->requireAuth();

        // Récupérer tous les calendriers de l'utilisateur avec leurs thèmes
        $calendars = $this->calendarModel->findAllWithThemesByUserId($_SESSION['user_id']);

        $this->render("calendar/index", [
            'title'      => 'Osez Noël - Mes calendriers',
            'cssFile'    => 'calendar',
            'jsFile'     => 'calendar',
            'calendars'  => $calendars, // ← Important !
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Afficher un calendrier
     */
    public function show(int $id): void
    {
        $this->requireAuth();

        $calendar = $this->calendarModel->findWithThemeById($id);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        // Vérifier que le calendrier appartient à l'utilisateur
        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Récupérer les surprises de ce calendrier
        $surprises = $this->surpriseModel->findByCalendarId($calendar['unique_id']);

        // Récupérer les jours ouverts
        $openedDays = $this->statisticsModel->getOpenedDays($calendar['calendar_id']);

        $this->render("calendar/show", [
            'title'      => 'Osez Noël - ' . htmlspecialchars($calendar['title']),
            'cssFile'    => 'calendar',
            'jsFile'     => 'calendar',
            'calendar'   => $calendar,
            'surprises'  => $surprises,
            'openedDays' => $openedDays,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Formulaire de création (GET)
     */
    public function create(): void
    {
        $this->requireAuth();

        // Récupérer tous les thèmes disponibles pour le formulaire
        $themes = $this->themeModel->findAll();

        $this->render("calendar/create", [
            'title'      => 'Osez Noël - Créer un calendrier',
            'cssFile'    => 'calendar',
            'jsFile'     => 'calendar-create',
            'themes'     => $themes, // ← Pour le select des thèmes
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Enregistrer un nouveau calendrier avec toutes ses surprises (POST)
     */
    public function store(): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/calendars/create');
            return;
        }

        // Validation
        $title   = $this->sanitize($_POST['title'] ?? '');
        $themeId = (int) ($_POST['theme_id'] ?? 0);

        if (empty($title)) {
            $this->flash('error', 'Le titre est obligatoire.');
            $this->redirect('/calendars/create');
            return;
        }

        if ($themeId <= 0) {
            $this->flash('error', 'Veuillez sélectionner un thème.');
            $this->redirect('/calendars/create');
            return;
        }

        try {
            // Créer le calendrier
            $calendarId = $this->calendarModel->create([
                'user_id'     => $_SESSION['user_id'], // ← Dans le tableau
                'title'       => $title,
                'theme_id'    => $themeId,
                'unique_id'   => $this->calendarModel->generateUniqueId(),
                'share_token' => $this->calendarModel->generateShareToken(),
            ]);

            // Récupérer le calendrier créé pour avoir le unique_id
            $calendar = $this->calendarModel->findById($calendarId);

            // 2. Créer toutes les surprises
            // Les surprises sont envoyées sous forme de tableau
            // $_POST['surprises'] = [
            //     1 => ['type' => 'text', 'content' => '...'],
            //     2 => ['type' => 'image', 'content' => '...'],
            //     ...
            // ]

            if (! empty($_POST['surprises']) && is_array($_POST['surprises'])) {
                foreach ($_POST['surprises'] as $day => $surpriseData) {
                    $day = (int) $day;

                    // Vérifier que le jour est valide
                    if ($day < 1 || $day > 24) {
                        continue;
                    }

                    // Vérifier qu'il y a du contenu
                    $type    = $this->sanitize($surpriseData['type'] ?? '');
                    $content = $this->sanitize($surpriseData['content'] ?? '');

                    if (empty($type) || empty($content)) {
                        continue; // Passer cette surprise
                    }

                    // Créer la surprise
                    $this->surpriseModel->create([
                        'calendar_id' => $calendar['unique_id'],
                        'day'         => $day,
                        'type'        => $type,
                        'content'     => $content,
                    ]);
                }
            }

            $this->flash('success', 'Calendrier créé avec succès !');
            $this->redirect('/calendars/' . $calendarId);

        } catch (\Exception $e) {
            error_log("Erreur création calendrier: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la création. Veuillez réessayer.');
            $this->redirect('/calendars/create');
        }
    }

    /**
     * Formulaire de modification (GET)
     */
    public function edit(int $id): void
    {
        $this->requireAuth();

        $calendar = $this->calendarModel->findById($id);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        // Vérifier que le calendrier appartient à l'utilisateur
        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Récupérer tous les thèmes pour le formulaire
        $themes = $this->themeModel->findAll();

        $this->render("calendar/edit", [
            'title'      => 'Osez Noël - Modifier le calendrier',
            'cssFile'    => 'calendar',
            'jsFile'     => 'calendar',
            'calendar'   => $calendar,
            'themes'     => $themes,
            'csrf_token' => $this->generateCsrfToken(),
        ]);
    }

    /**
     * Mettre à jour un calendrier (POST)
     */
    public function update(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/calendars/' . $id . '/edit');
            return;
        }

        $calendar = $this->calendarModel->findById($id);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        // Vérifier que le calendrier appartient à l'utilisateur
        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Validation
        $title   = $this->sanitize($_POST['title'] ?? '');
        $themeId = (int) ($_POST['theme_id'] ?? 0);

        if (empty($title)) {
            $this->flash('error', 'Le titre est obligatoire.');
            $this->redirect('/calendars/' . $id . '/edit');
            return;
        }

        try {
            $this->calendarModel->update($id, [
                'title'    => $title,
                'theme_id' => $themeId,
            ]);

            $this->flash('success', 'Calendrier mis à jour avec succès !');
            $this->redirect('/calendars/' . $id);

        } catch (\Exception $e) {
            error_log("Erreur mise à jour calendrier: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la mise à jour. Veuillez réessayer.');
            $this->redirect('/calendars/' . $id . '/edit');
        }
    }

    /**
     * Supprimer un calendrier (POST)
     */
    public function delete(int $id): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/calendars');
            return;
        }

        $calendar = $this->calendarModel->findById($id);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        // Vérifier que le calendrier appartient à l'utilisateur
        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        try {
            // Supprimer toutes les surprises associées (MongoDB)
            $this->surpriseModel->deleteByCalendarId($calendar['unique_id']);

            // Supprimer le calendrier (MySQL - les stats sont supprimées automatiquement avec CASCADE)
            $this->calendarModel->delete($id);

            $this->flash('success', 'Calendrier supprimé avec succès !');
            $this->redirect('/calendars');

        } catch (\Exception $e) {
            error_log("Erreur suppression calendrier: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la suppression. Veuillez réessayer.');
            $this->redirect('/calendars');
        }
    }

    /**
     * Voir les statistiques d'un calendrier
     */
    public function stats(int $id): void
    {
        $this->requireAuth();

        $calendar = $this->calendarModel->findById($id);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        // Vérifier que le calendrier appartient à l'utilisateur
        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Récupérer les statistiques
        $stats    = $this->statisticsModel->getCalendarStats($calendar['calendar_id']);
        $openings = $this->statisticsModel->findByCalendarId($calendar['calendar_id']);

        $this->render("calendar/stats", [
            'title'    => 'Statistiques - ' . htmlspecialchars($calendar['title']),
            'cssFile'  => 'calendar',
            'calendar' => $calendar,
            'stats'    => $stats,
            'openings' => $openings,
        ]);
    }

    /**
     * Obtenir le lien de partage d'un calendrier
     */
    public function share(int $id): void
    {
        $this->requireAuth();

        $calendar = $this->calendarModel->findById($id);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        // Vérifier que le calendrier appartient à l'utilisateur
        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Générer l'URL de partage
        $shareUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/share/' . $calendar['share_token'];

        $this->render("calendar/share", [
            'title'    => 'Partager - ' . htmlspecialchars($calendar['title']),
            'cssFile'  => 'calendar',
            'calendar' => $calendar,
            'shareUrl' => $shareUrl,
        ]);
    }
}
