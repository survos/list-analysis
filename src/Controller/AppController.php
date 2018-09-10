<?php

namespace App\Controller;

use Survos\LandingBundle\LandingService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/landing", name="landing")
     */
    public function index(LandingService $landingService)
    {
        return $this->render('test_menu_index.html.twig', [
            'entities' => $landingService->getEntities()
        ]);
    }
}
