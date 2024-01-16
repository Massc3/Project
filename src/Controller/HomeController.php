<?php

namespace App\Controller;

use App\Repository\AvisRepository;
use App\Repository\EventRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(CategoryRepository $categoryRepository, AvisRepository $avisRepository, EventRepository $eventRepository): Response
    {
        // $category = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $categories = $categoryRepository->findBy([], ['nameCategory' => 'ASC']);
        $lastEvents = $eventRepository->findLastEvents();
        $lastAvis = $avisRepository->findLastAvis();
        // dd($categories);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('home/index.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'categories' => $categories,
            'lastEvents' => $lastEvents,
            'lastAvis' => $lastAvis,
        ]);
    }

    #[Route('/home', name: 'app_base')]
    public function base(CategoryRepository $categoryRepository): Response
    {
        // $category = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $categories = $categoryRepository->findBy([], ['nameCategory' => 'ASC']);
       
        // dd($categories);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('home/.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'categories' => $categories,
           
        ]);
    }
    // #[Route('/home/event', name: 'app_event')]
    // public function lastEvents(EventRepository $eventRepository): Response
    // {
    //     // Récupérer les 6 derniers événements
    //     $lastEvents = $eventRepository->findLastEvents();

    //     return $this->render('home/index.html.twig', [
    //         'lastEvents' => $lastEvents,
    //     ]);
    // }
}
