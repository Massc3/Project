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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class EventController extends AbstractController
{
    // #[Route('/admin/event', name: 'app_event')]
     // Route pour afficher la liste des événements
     #[Route('/event', name: 'app_event')]
     public function index(EventRepository $eventRepository, CategoryRepository $categoryRepository, EntityManagerInterface $entityManager): Response
     {
         // Récupération des événements et des catégories depuis les référentiels
         $events = $eventRepository->findBy([], ['description' => 'ASC']);
         $categories = $categoryRepository->findBy([], ['nameCategory' => 'ASC']);
 
         // Rendu de la vue index avec les événements et les catégories
         return $this->render('event/index.html.twig', [
             'events' => $events,
             'categories' => $categories,
         ]);
     }
 
     // Routes pour créer ou éditer des événements
     #[Route('^/admin/{themeId}/event/new', name: 'add_event')]
     #[Route('/admin/event/{id}/edit', name: 'edit_event')]
     public function new_edit(Event $event = null, Request $request, EntityManagerInterface $entityManager, ThemeRepository $themeRepository, $themeId = null, PictureService $pictureService): Response
     {
         // Si aucun événement n'est fourni, créez-en un nouveau avec une image par défaut
         if (!$event) {
             $event = new Event();
             $event->setPicture('parc.jpg');
         }
 
         // Si un themeId est fourni, associez-le à l'événement
         if ($themeId !== null) {
             $theme = $themeRepository->find($themeId);
 
             if ($theme) {
                 $event->setTheme($theme);
             }
         }
 
         // Création et traitement du formulaire pour ajouter/éditer des événements
         $form = $this->createForm(EventType::class, $event);
         $form->handleRequest($request);
 
         // Récupération des informations sur le thème en utilisant le themeId fourni
         $theme = $themeRepository->find($themeId);
 
         // Obtenez l'utilisateur actuellement connecté
         $currentUser = $this->getUser();
 
         // Gérer la soumission du formulaire
         if ($form->isSubmitted() && $form->isValid()) {
             // Assurez-vous que l'utilisateur actuel est assigné à l'événement
             $event->setUser($currentUser);
 
             // ...
 
             // Persister l'événement dans la base de données
             $entityManager->persist($event);
             $entityManager->flush();
 
             // Rediriger vers l'URL du thème
             $themeUrl = $this->generateUrl('afficherDetail_theme', ['id' => $themeId]);
             return $this->redirect($themeUrl);
         }
 
         // Rendu de la vue du formulaire nouvel/édit événement
         return $this->render('event/new.html.twig', [
             'formAddEvent' => $form,
             'edit' => $event->getId()
         ]);
     }
 
     // Route pour supprimer un événement
     #[Route('/event/{id}/delete', name: 'delete_event')]
     public function delete(Event $event, EntityManagerInterface $entityManager)
     {
         // Supprimer l'événement de la base de données
         $entityManager->remove($event);
         $entityManager->flush();
 
         // Rediriger vers la liste des événements
         return $this->redirectToRoute('app_event');
     }
 
     // Route pour afficher les détails d'un événement
     #[Route('/event/{id}', name: 'afficherDetail_event')]
     public function afficherDetail(Event $event, EventRepository $eventRepository): Response
     {
         // Charger les participants de l'événement
         $event = $eventRepository->findEventWithParticipants($event->getId());
 
         // Récupérer l'image associée à l'événement
         $picture = $event->getPicture();
 
         // Rendu de la vue des détails de l'événement
         return $this->render('event/afficherDetail.html.twig', [
             'event' => $event,
             'participants' => $event->getParticipants(),
             'picture' => $picture
         ]);
     }
     
 
        // Limiter la participation d'un utilisateur à un événement à la fois
    #[Route('/event/{id}/participate', name: 'participate_event')]
    public function Participate(Event $event, EntityManagerInterface $entityManager, Request $request): Response
    {
        // Obtenez l'utilisateur actuellement connecté
        $currentUser = $this->getUser();

        // Vérifiez si l'utilisateur a atteint le nombre maximal de participants
        if ($event->getNote() !== null && count($event->getParticipants()) >= $event->getNote()) {
            $this->addFlash('error', 'Le nombre maximal de participants à cet événement a été atteint.');
        } else {
            try {
                // Ajoutez l'utilisateur en tant que participant et persistez les changements
                $event->addParticipant($currentUser);
                $entityManager->flush();
                $this->addFlash('success', 'Vous avez participé à l\'événement avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        // Récupérer l'URL de la page précédente et rediriger
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }



     #[Route('/event/{id}/withdraw', name: 'withdraw_event')]
    public function withdraw(Event $event, EntityManagerInterface $entityManager, Request $request): RedirectResponse
    {
        // Obtenez l'utilisateur actuellement connecté
        $currentUser = $this->getUser();

        // Vérifiez si l'utilisateur est déjà un participant
        if ($event->isParticipant($currentUser)) {
            try {
                // Retirez l'utilisateur en tant que participant et persistez les changements
                $event->removeParticipant($currentUser);
                $entityManager->flush();
                $this->addFlash('success', 'Vous vous êtes retiré de l\'événement avec succès.');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'Vous ne participez pas à cet événement.');
        }

        // Récupérer l'URL de la page précédente et rediriger
        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }
    // #[Route('/event/{id}/share', name: 'share_event')]
    // public function shareEvent(Event $event): Response
    // {
    //     // Générer le lien partageable, par exemple, avec la route "afficherDetail_event"
    //     $sharedLink = $this->generateUrl('afficherDetail_event', ['id' => $event->getId()], UrlGeneratorInterface::ABSOLUTE_URL);

        
    //     return $this->render('event/share_event.html.twig', [
    //         'event' => $event,
    //         'sharedLink' => $sharedLink,
    //     ]);
    // }
}
