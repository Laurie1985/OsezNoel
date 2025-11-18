<?php
namespace App\Controller;

class HomeController extends BaseController
{
    /**
     * Page d'accueil
     */
    public function index(): void
    {
        $this->render('home/index', [
            'title'   => 'Osez Noël - Accueil',
            'cssFile' => 'home',
        ]);
    }

}
