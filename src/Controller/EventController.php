<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Service\PictureService;
use App\Repository\EventRepository;
use App\Repository\ThemeRepository;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    // #[Route('/admin/event', name: 'app_event')]
    #[Route('/admin/event', name: 'app_event')]
    public function index(EventRepository $eventRepository, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
    {

        $events = $eventRepository->findBy([], ['description' => 'ASC']);
        $categories = $categoryRepository->findBy([], ['nameCategory' => 'ASC']);

        // dd($events);
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'categories' => $categories,

        ]);
    }


    #[Route('^/admin/{themeId}/event/new', name: 'add_event')]
    #[Route('/event/{id}/edit', name: 'edit_event')]
    public function new_edit(Event $event = null, Request $request, EntityManagerInterface $entityManager, ThemeRepository $themeRepository, $themeId = null, PictureService $pictureService): Response
    {
        if (!$event) {
            $event = new Event();
            $event->setPicture('parc.jpg');
        }

        if ($themeId !== null) {
            $theme = $themeRepository->find($themeId);

            if ($theme) {
                $event->setTheme($theme);
            }
        }

        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        $theme = $themeRepository->find($themeId);

        $currentUser = $this->getUser();

        if ($form->isSubmitted() && $form->isValid()) {
            // Assurez-vous que l'utilisateur actuel est assigné à l'événement
            $event->setUser($currentUser);

            $picture = $form->get('picture')->getData();
            $eventDebut = $form->get('dateDebut')->getData();

            if ($eventDebut < new \DateTime()) {
                $this->addFlash('error', 'La date de début de l\'événement ne peut pas être antérieure à la date actuelle.');
                return $this->redirectToRoute('add_event', ['themeId' => $themeId]);
            }

            $event->setDateDebut($eventDebut);

            if ($picture instanceof UploadedFile) {
                $picture = $pictureService->upload($picture);
                $event->setPicture($picture);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('app_event');
        }

        return $this->render('event/new.html.twig', [
            'formAddEvent' => $form,
            'edit' => $event->getId()
        ]);
    }

    // #[Route('/{themeId}/event/new', name: 'add_event')]
    // #[Route('/event/{id}/edit', name: 'edit_event')]
    // public function new_edit(Event $event = null, Request $request, EntityManagerInterface $entityManager, ThemeRepository $themeRepository, $themeId = null, PictureService $pictureService): Response
    // {

    //     if (!$event) {
    //         $event = new Event();
    //         $event->setPicture('parc.jpg');
    //     }

    //     if ($themeId !== null) {
    //         // Vous pouvez utiliser $themeRepository pour récupérer le thème correspondant à $themeId
    //         $theme = $themeRepository->find($themeId);

    //         // Assurez-vous que le thème existe avant de l'associer à l'événement
    //         if ($theme) {
    //             $event->setTheme($theme);
    //         }
    //     }
    //     // on creer le formulaire a partir de eventType
    //     $form = $this->createForm(EventType::class, $event);
    //     // on prend en charge la requete demandé
    //     $form->handleRequest($request);
    //     // si le formulaire est rempli et qu'il est valide
    //     $theme = $themeRepository->find($themeId);

    //     // Utilisez $this->getUser() pour récupérer l'utilisateur actuel
    //     $currentUser = $this->getUser();


    //     if ($form->isSubmitted() && $form->isValid()) {

    //         // Utilisez $currentUser comme l'utilisateur actuel
    //         $event->setUser($currentUser);


    //         $picture = $form->get('picture')->getData();
    //         $eventDebut = $form->get('dateDebut')->getData();

    //         if ($eventDebut < new \DateTime()) {
    //             $this->addFlash('error', 'La date de début de l\'événement ne peut pas être antérieure à la date actuelle.');
    //             return $this->redirectToRoute('add_event', ['themeId' => $themeId]);
    //         }

    //         // Utilisez $eventDebut pour la date de début de l'événement
    //         $event->setDateDebut($eventDebut);
    //         // on recupere les données du formaulaire 
    //         // $event = $form->getData();

    //         if ($picture instanceof UploadedFile) {
    //             $picture = $pictureService->upload($picture);
    //             $event->setPicture($picture);
    //         }

    //         // dd($event);
    //         // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
    //         $entityManager->persist($event);
    //         // // equivalence du execute en PDO
    //         $entityManager->flush();
    //         // on fait une redirection vers notre liste event 
    //         return $this->redirectToRoute('app_event');
    //     }
    //     // dd($form);   
    //     return $this->render('event/new.html.twig', [
    //         'formAddEvent' => $form,
    //         'edit' => $event->getId()
    //     ]);
    // }


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

    #[Route('/event/{id}/participate', name: 'participate_event')]
    public function participate(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        $currentUser = $this->getUser();

        try {
            $event->addParticipant($currentUser);
            $entityManager->flush();
            $this->addFlash('success', 'Vous avez participé à l\'événement avec succès.');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
    // limiter la participation d'un user qu'a un event a la fois 
}
