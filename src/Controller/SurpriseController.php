<?php
namespace App\Controller;

use App\Model\Calendar;
use App\Model\Surprise;

class SurpriseController extends BaseController
{
    private Calendar $calendarModel;
    private Surprise $surpriseModel;

    public function __construct()
    {
        parent::__construct();
        $this->calendarModel = new Calendar();
        $this->surpriseModel = new Surprise();
    }

    /**
     * Mettre à jour une surprise existante (après création du calendrier)
     */
    public function update(int $calendarId, int $day): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/calendars/' . $calendarId);
            return;
        }

        // Vérifier la propriété du calendrier
        $calendar = $this->calendarModel->findById($calendarId);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Récupérer les données du formulaire
        $type    = $this->sanitize($_POST['type'] ?? '');
        $content = $this->sanitize($_POST['content'] ?? '');

        // Validation
        if (empty($type) || empty($content)) {
            $this->flash('error', 'Type et contenu obligatoires.');
            $this->redirect('/calendars/' . $calendarId);
            return;
        }

        // Validation du jour
        if ($day < 1 || $day > 24) {
            $this->flash('error', 'Jour invalide.');
            $this->redirect('/calendars/' . $calendarId);
            return;
        }

        try {
            // Récupérer la surprise existante
            $surprise = $this->surpriseModel->findByCalendarIdAndDay($calendar['unique_id'], $day);

            if ($surprise) {
                // Mise à jour de la surprise existante
                $this->surpriseModel->update((string) $surprise['_id'], [
                    'type'    => $type,
                    'content' => $content,
                ]);
                $this->flash('success', 'Surprise du jour ' . $day . ' mise à jour avec succès !');
            } else {
                // Créer la surprise si elle n'existait pas
                $this->surpriseModel->create([
                    'calendar_id' => $calendar['unique_id'],
                    'day'         => $day,
                    'type'        => $type,
                    'content'     => $content,
                ]);
                $this->flash('success', 'Surprise du jour ' . $day . ' ajoutée avec succès !');
            }

            $this->redirect('/calendars/' . $calendarId);

        } catch (\Exception $e) {
            error_log("Erreur mise à jour surprise: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de l\'enregistrement. Veuillez réessayer.');
            $this->redirect('/calendars/' . $calendarId);
        }
    }

    /**
     * Supprimer une surprise
     */
    public function delete(int $calendarId, int $day): void
    {
        $this->requireAuth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || ! $this->requireCsrfToken()) {
            $this->flash('error', 'Requête invalide.');
            $this->redirect('/calendars/' . $calendarId);
            return;
        }

        // Vérifier la propriété du calendrier
        $calendar = $this->calendarModel->findById($calendarId);

        if (! $calendar) {
            $this->flash('error', 'Calendrier introuvable.');
            $this->redirect('/calendars');
            return;
        }

        if ($calendar['user_id'] !== $_SESSION['user_id']) {
            $this->flash('error', 'Accès interdit.');
            $this->redirect('/calendars');
            return;
        }

        // Validation du jour
        if ($day < 1 || $day > 24) {
            $this->flash('error', 'Jour invalide.');
            $this->redirect('/calendars/' . $calendarId);
            return;
        }

        try {
            // Récupérer la surprise
            $surprise = $this->surpriseModel->findByCalendarIdAndDay($calendar['unique_id'], $day);

            if (! $surprise) {
                $this->flash('error', 'Surprise du jour ' . $day . ' introuvable.');
                $this->redirect('/calendars/' . $calendarId);
                return;
            }

            // Supprimer la surprise
            $this->surpriseModel->delete((string) $surprise['_id']);

            $this->flash('success', 'Surprise du jour ' . $day . ' supprimée avec succès !');
            $this->redirect('/calendars/' . $calendarId);

        } catch (\Exception $e) {
            error_log("Erreur suppression surprise: " . $e->getMessage());
            $this->flash('error', 'Erreur lors de la suppression. Veuillez réessayer.');
            $this->redirect('/calendars/' . $calendarId);
        }
    }
}
