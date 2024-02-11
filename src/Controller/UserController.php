<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Repository\EventRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserController extends AbstractController
{

    #[Route('/user', name: 'app_user_dashboard')]
    public function dashboard(EventRepository $eventRepository, UserRepository $userRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $userSession = $this->getUser();

        // Vérifier si l'utilisateur est authentifié
        if (!$userSession) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer les utilisateurs mis en favoris par l'utilisateur connecté
        $userFavoris = $userRepository->findOneBy(['email' => $userSession->getUserIdentifier()])->getFavoris();
        // Récupérer tous les événements
        $events = $eventRepository->findAll();


        // Récupérer l'utilisateur connecté
        $userEmail = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $userEmail]);

        return $this->render('user/index.html.twig', [
            'events' => $events,
            'user' => $user,
            'userFavoris' => $userFavoris
        ]);
    }



    /***********mettre une page profil en favoris *************/
    #[Route('/user/{id}', name: 'view_user_profile')]
    public function viewUserProfile($id, UserRepository $userRepository): Response
    {
        $user = $userRepository->findOneBy(['id' => $id]);
        // dd($user);
        if ($this->getUser()) {
            // verifier si l'utilisateur actuel à mis l'autre utilisateur en favoris 
            // $isFavorite = $user && $user->getFavoris()->contains($user);

            return $this->render('user/page.html.twig', [
                'user' => $user,
                // 'isFavorite' => $isFavorite,
            ]);
        }
        return $this->redirectToRoute('app_home');
    }


    #[Route('/user/{id}/favorite', name: 'add_favorite')]
    public function addFavorite($id, UserRepository $userRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur par l'id
        $userSource = $userRepository->findOneBy(['id' => $id]);

        $userIdentifier = $this->getUser()->getUserIdentifier();
        $user = $userRepository->findOneBy(['email' => $userIdentifier]);
        // Vérifier si l'utilisateur connecté n'est pas le même que l'utilisateur cible
        if ($userSource->getId() !== $user->getId()) {
            // Vérifier si l'utilisateur est déjà ajouté en favori
            if (!$userSource->getFavoris()->contains($user)) {
                // Ajouter l'utilisateur cible aux favoris de l'utilisateur source
                $userSource->addFavori($user);

                // Enregistrer les modifications dans la base de données
                $entityManager->persist($userSource);
                $entityManager->flush();

                $this->addFlash('success', 'Utilisateur ajouté en favori avec succès.');
            } else {
                $this->addFlash('warning', 'Cet utilisateur est déjà en favori.');
            }
        } else {
            $this->addFlash('warning', 'Vous ne pouvez pas vous ajouter en favori.');
        }

        // Rediriger l'utilisateur vers la page appropriée
        return $this->redirectToRoute('view_user_profile', ['id' => $user->getId()]);
    }

    #[Route('/user/profile', name: 'user_profile')]
    public function profile(User $user, UserRepository $userRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $this->getUser();
        $userReal = $userRepository->findOneBy(['email' => $user->getUserIdentifier()]);
        // Vérifier si l'utilisateur est authentifié
        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour accéder à cette page.');
        }

        // Récupérer les utilisateurs mis en favoris par l'utilisateur connecté
        $userFavoris = $userReal->getFavoris();

        return $this->render('user/profile.html.twig', [
            'user' => $user,
            'userFavoris' => $userFavoris,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete_profile')]
    public function index($id, EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher, TokenStorageInterface $tokenStorage, UserRepository $userRepository): Response
    {

        //instanciate the user
        $user = $userRepository->findOneBy(['id' => $id]);

        //the user is equal to the user in seesion
        if ($user == $this->getUser()) {
            $events = $user->getEvent();
            $participations = $user->getParticipant();
            // dd($events);
            $user->setIsVerified(false);
            foreach ($participations as $participant) {
                // dd($participant);
                $participant->removeParticipant($user);
                // $em->remove($participant);
            }

            foreach ($events as $event) {
                // dd($event);
                $user->removeEvent($event);
                $user->removeParticipant($event);
                $em->remove($event);
            }
            $user->setPseudo('Profile supprimé');

            $newPassword = bin2hex(random_bytes(8));
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashedPassword);

            $uniqueValue = 'deleted_user_' . uniqid();
            $user->setEmail($uniqueValue);

            $em->persist($user);
            $em->flush();

            $tokenStorage->setToken(null);
            //let him see his profile
            $this->addFlash('success', 'Your profile was succesfully deleted.');
            return $this->redirectToRoute('app_register');
        }

        //if the user was different from the current user in seesion send back to home and add flash message
        $this->addFlash('danger', 'You dont have access to this page.');
        return $this->redirectToRoute('app_home');
    }

    // #[Route('/user/delete', name: 'delete_user_profile')]
    // public function deleteProfile(Request $request): Response
    // {
    //     // Récupérer l'EntityManager
    // $entityManager = $this->getUser()->getManager();

    // // Récupérer l'utilisateur actuellement connecté
    // $user = $this->getUser(); 

    // // Supprimer l'utilisateur de la base de données
    // $entityManager->remove($user);
    // $entityManager->flush();

    // // Rediriger vers une page de confirmation ou autre
    // return $this->redirectToRoute('app_register');
    // }
}
