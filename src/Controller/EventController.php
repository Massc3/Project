<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(EventRepository $eventRepository): Response
    {
        // $event = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $events = $eventRepository->findBy([], ['description' => 'ASC']);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('home/index.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'event' => $events
        ]);
    }
    #[Route('/event/new', name: 'add_event')]
    #[Route('/event/{id}/edit', name: 'edit_event')]
    public function new_edit(Event $event = null, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$event) {
            $event = new Event();
        }


        // on creer le formulaire a partir de eventType
        $form = $this->createForm(EventType::class, $event);
        // on prend en charge la requete demandé
        $form->handleRequest($request);
        // si le formulaire est rempli et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // on recupere les données du formaulaire 
            $event = $form->getData();
            // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
            $entityManager->persist($event);
            // equivalence du execute en PDO
            $entityManager->flush();
            // on fait une redirection vers notre liste event 
            return $this->redirectToRoute('app_event');
        }
        return $this->render('event/new.html.twig', [
            'formAddEvent' => $form,
            'edit' => $event->getId()
        ]);
    }


    #[Route('/event/{id}/delete', name: 'delete_event')]
    public function delete(Event $event, EntityManagerInterface $entityManager)

    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event');
    }


    #[Route('/event/{id}', name: 'afficherDetail_event')]
    public function afficherDetail(Event $event): Response
    {
        return $this->render('event/afficherDetail.html.twig', [
            // on fais passer la variable event a laquelle on lui donne la valeur event
            'event' => $event
        ]);
    }
}
