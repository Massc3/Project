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
    #[Route('/admin/event/{id}/edit', name: 'edit_event')]
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

            // return $this->redirectToRoute('app_event');
            // $referer = $request->headers->get('referer');
            // return $this->redirect($referer);
            $themeUrl = $this->generateUrl('afficherDetail_theme', ['id' => $themeId]);

            // Rediriger vers l'URL du thème
            return $this->redirect($themeUrl);
        }

        return $this->render('event/new.html.twig', [
            'formAddEvent' => $form,
            'edit' => $event->getId()
        ]);
    }



    #[Route('/admin/event/{id}/delete', name: 'delete_event')]
    public function delete(Event $event, EntityManagerInterface $entityManager)

    {
        $entityManager->remove($event);
        $entityManager->flush();

        return $this->redirectToRoute('app_event');
    }



    #[Route('/event/{id}', name: 'afficherDetail_event')]
    public function afficherDetail(Event $event, EventRepository $eventRepository): Response
    {
        // Charger les participants de l'événement
        $event = $eventRepository->findEventWithParticipants($event->getId());

        $picture = $event->getPicture();

        return $this->render('event/afficherDetail.html.twig', [
            'event' => $event,
            'participants' => $event->getParticipants(),
            'picture' => $picture
        ]);
    }

    // limiter la participation d'un user qu'a un event a la fois 
    #[Route('/event/{id}/participate', name: 'participate_event')]
    public function Participate(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        $currentUser = $this->getUser();

        // Vérifier si l'utilisateur a atteint le nombre maximal de participants
        if ($event->getNote() !== null && count($event->getParticipants()) >= $event->getNote()) {
            $this->addFlash('error', 'Le nombre maximal de participants à cet événement a été atteint.');
        } else {
            try {
                $event->addParticipant($currentUser);
                $entityManager->flush();
                $this->addFlash('success', 'Vous avez participé à l\'événement avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    #[Route('/event/{id}/share', name: 'share_event')]
    public function shareEvent(Event $event): Response
    {
        // Générer le lien partageable, par exemple, avec la route "afficherDetail_event"
        $sharedLink = $this->generateUrl('afficherDetail_event', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        
        return $this->render('event/share_event.html.twig', [
            'event' => $event,
            'sharedLink' => $sharedLink,
        ]);
    }
}
