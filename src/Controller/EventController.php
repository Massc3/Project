<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\PictureService;
use App\Repository\EventRepository;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    #[Route('/event', name: 'app_event')]
    public function index(EventRepository $eventRepository, EntityManagerInterface $entityManager): Response
    {

        $events = $eventRepository->findBy([], ['description' => 'ASC']);

        // dd($events);
        return $this->render('event/index.html.twig', [
            'events' => $events,

        ]);
    }

    #[Route('/{themeId}/event/new', name: 'add_event')]
    #[Route('/event/{id}/edit', name: 'edit_event')]
    public function new_edit(Event $event = null, Request $request, EntityManagerInterface $entityManager, ThemeRepository $themeRepository, $themeId = null, PictureService $pictureService): Response
    {

        if (!$event) {
            $event = new Event();
            $event->setPicture('parc.jpg');
        }

        if ($themeId !== null) {
            // Vous pouvez utiliser $themeRepository pour récupérer le thème correspondant à $themeId
            $theme = $themeRepository->find($themeId);
    
            // Assurez-vous que le thème existe avant de l'associer à l'événement
            if ($theme) {
                $event->setTheme($theme);
            }
        }
        // on creer le formulaire a partir de eventType
        $form = $this->createForm(EventType::class, $event);
        // on prend en charge la requete demandé
        $form->handleRequest($request);
        // si le formulaire est rempli et qu'il est valide
        $theme = $themeRepository->find($themeId);

        if ($form->isSubmitted() && $form->isValid()) {
            $picture = $form->get('picture')->getData();

            // on recupere les données du formaulaire 
            $event = $form->getData();

            if ($picture instanceof UploadedFile) {
                $picture = $pictureService->upload($picture);
                $event->setPicture($picture);
            }
    
            // dd($event);
            // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
            $entityManager->persist($event);
            // // equivalence du execute en PDO
            $entityManager->flush();
            // on fait une redirection vers notre liste event 
            return $this->redirectToRoute('app_event');
        }
        // dd($form);   
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
    public function afficherDetail(Event $event, EntityManagerInterface $entityManager): Response
    {
        $picture = $event->getPicture();
        return $this->render('event/afficherDetail.html.twig', [
            // on fais passer la variable event a laquelle on lui donne la valeur event
            'event' => $event,
            'picture' => $picture
        ]);
    }
}
