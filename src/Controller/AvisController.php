<?php

namespace App\Controller;

use App\Entity\Avis;
use App\Form\AvisType;
use App\Repository\AvisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class AvisController extends AbstractController
{
    #[Route('/avis', name: 'app_avis')]
    public function index(AvisRepository $avisRepository): Response
    {
        // $avis = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $avis = $avisRepository->findBy([], ['text' => 'ASC']);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('home/index.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'avis' => $avis
        ]);
    }
    #[Route('/avis/new', name: 'add_avis')]
    #[Route('/avis/{id}/edit', name: 'edit_avis')]
    public function new_edit(Avis $avis = null, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$avis) {
            $avis = new Avis();
        }


        // on creer le formulaire a partir de avisType
        $form = $this->createForm(AvisType::class, $avis);
        // on prend en charge la requete demandé
        $form->handleRequest($request);
        // si le formulaire est rempli et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // on recupere les données du formaulaire 
            $avis = $form->getData();
            // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
            $entityManager->persist($avis);
            // equivalence du execute en PDO
            $entityManager->flush();
            // on fait une redirection vers notre liste avis 
            return $this->redirectToRoute('app_avis');
        }
        return $this->render('avis/new.html.twig', [
            'formAddAvis' => $form,
            'edit' => $avis->getId()
        ]);
    }


    #[Route('/avis/{id}/delete', name: 'delete_avis')]
    public function delete(Avis $avis, EntityManagerInterface $entityManager)

    {
        $entityManager->remove($avis);
        $entityManager->flush();

        return $this->redirectToRoute('app_avis');
    }


    #[Route('/avis/{id}', name: 'afficherDetail_avis')]
    public function afficherDetail(Avis $avis): Response
    {
        return $this->render('avis/afficherDetail.html.twig', [
            // on fais passer la variable avis a laquelle on lui donne la valeur avis
            'avis' => $avis
        ]);
    }
    // #[Route('/home', name: 'lastAvis')]
    // public function lastAvis(EntityManagerInterface $entityManager): Response
    // {
    //     // Récupérer les 6 derniers événements
    //     $lastAvis = $entityManager->getRepository(avis::class)
    //         ->findBy([], ['dateDebut' => 'DESC'], 3);

    //     return $this->render('home/index.html.twig', [
    //         'lastAvis' => $lastAvis,
    //     ]);
    // }
}
