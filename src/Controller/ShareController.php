<?php
namespace App\Controller;

use App\Model\Calendar;
use App\Model\Statistics;
use App\Model\Surprise;
use App\Model\Theme;

class ShareController extends BaseController
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
     * Afficher le calendrier partagé (grille 24 cases)
     * ACCESSIBLE SANS AUTHENTIFICATION (public)
     */
    public function show(string $token): void
    {
        // Récupérer le calendrier via le token de partage
        $calendar = $this->calendarModel->findByShareToken($token);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable. Vérifiez le lien.');
            $this->redirect('/');
            return;
        }

        // Récupérer le thème
        $theme = $this->themeModel->findById($calendar['theme_id']);

        // Récupérer toutes les surprises du calendrier
        $surprises = $this->surpriseModel->findByCalendarId($calendar['unique_id']);

        // Créer un tableau associatif jour => surprise
        $surprisesByDay = [];
        foreach ($surprises as $surprise) {
            $surprisesByDay[$surprise['day']] = $surprise;
        }

        // Récupérer les jours déjà ouverts
        $openedDays = $this->statisticsModel->getOpenedDays($calendar['calendar_id']);

        // Date actuelle
        $currentDay   = (int) date('d');
        $currentMonth = (int) date('m');

        // On est en décembre ?
        $isDecember = ($currentMonth === 12);

        $this->render('share/calendar', [
            'title'          => 'Calendrier de l\'Avent - ' . htmlspecialchars($calendar['title']),
            'cssFile'        => 'share',
            'jsFile'         => 'share',
            'calendar'       => $calendar,
            'theme'          => $theme,
            'surprisesByDay' => $surprisesByDay,
            'openedDays'     => $openedDays,
            'currentDay'     => $currentDay,
            'isDecember'     => $isDecember,
            'token'          => $token,
        ]);
    }

    /**
     * Ouvrir une case et afficher la surprise
     * ACCESSIBLE SANS AUTHENTIFICATION (public)
     */
    public function open(string $token, int $day): void
    {
        // Récupérer le calendrier
        $calendar = $this->calendarModel->findByShareToken($token);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable. Vérifiez le lien.');
            $this->redirect('/');
            return;
        }

        // Validation du jour
        if ($day < 1 || $day > 24) {
            $this->flash('error', 'Jour invalide.');
            $this->redirect('/share/' . $token);
            return;
        }

        // Vérifier qu'on est en décembre
        $currentMonth = (int) date('m');
        $currentDay   = (int) date('d');

        // Si on n'est pas en décembre, on peut quand même ouvrir (mode démo)
        // Sinon, vérifier que c'est le bon jour ou un jour passé
        if ($currentMonth === 12 && $day > $currentDay) {
            $this->flash('error', 'Cette case ne peut pas encore être ouverte ! Patience jusqu\'au ' . $day . ' décembre. 🎄');
            $this->redirect('/share/' . $token);
            return;
        }

        // Récupérer la surprise
        $surprise = $this->surpriseModel->findByCalendarIdAndDay($calendar['unique_id'], $day);

        if (! $surprise) {
            $this->flash('error', 'Aucune surprise pour ce jour. 😢');
            $this->redirect('/share/' . $token);
            return;
        }

        // Enregistrer l'ouverture dans les statistiques (si pas déjà ouverte)
        try {
            $alreadyOpened = $this->statisticsModel->isOpened($calendar['calendar_id'], $day);

            if (! $alreadyOpened) {
                $this->statisticsModel->recordOpening($calendar['calendar_id'], $day);
            }
        } catch (\Exception $e) {
            // Si erreur (doublon), ce n'est pas grave, on continue
            error_log("Erreur enregistrement stats: " . $e->getMessage());
        }

        // Récupérer le thème pour l'affichage
        $theme = $this->themeModel->findById($calendar['theme_id']);

        // Afficher la surprise
        $this->render('share/surprise', [
            'title'    => 'Surprise du jour ' . $day . ' - ' . htmlspecialchars($calendar['title']),
            'cssFile'  => 'share',
            'jsFile'   => 'share',
            'calendar' => $calendar,
            'theme'    => $theme,
            'surprise' => $surprise,
            'day'      => $day,
            'token'    => $token,
        ]);
    }
}
