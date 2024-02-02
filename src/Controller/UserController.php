<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class UserController extends AbstractController
{
   
    #[Route('/user', name: 'app_user_dashboard')]
    public function dashboard(EventRepository $eventRepository, UserRepository $userRepository): Response
    {
        // Récupérer tous les événements
        $events = $eventRepository->findAll();

        // Récupérer l'utilisateur connecté
        $userEmail = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $userEmail]);

        return $this->render('user/index.html.twig', [
            'events' => $events,
            'user' => $user,
        ]);
    }
}
