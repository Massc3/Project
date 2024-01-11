<?php

namespace App\Controller;

use App\Entity\Theme;
use App\Form\ThemeType;
use App\Repository\ThemeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ThemeController extends AbstractController
{
    #[Route('/theme', name: 'app_theme')]
    public function index(ThemeRepository $themeRepository): Response
    {
        // $theme = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $themes = $themeRepository->findBy([], ['title' => 'ASC']);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('theme/index.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'themes' => $themes
        ]);
    }

    #[Route('/theme/new', name: 'add_theme')]
    #[Route('/theme/{id}/edit', name: 'edit_theme')]
    public function new_edit(Theme $theme = null, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$theme) {
            $theme = new Theme();
        }
        // dd($theme->getTitle());

        // on creer le formulaire a partir de themeType
        $form = $this->createForm(ThemeType::class, $theme);
        // on prend en charge la requete demandé
        $form->handleRequest($request);
        // si le formulaire est rempli et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // on recupere les données du formaulaire 
            $theme = $form->getData();
            // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
            $entityManager->persist($theme);
            // equivalence du execute en PDO
            $entityManager->flush();
            // on fait une redirection vers notre liste theme 
            return $this->redirectToRoute('app_theme');
        }
        return $this->render('theme/new.html.twig', [
            'formAddTheme' => $form,
            'edit' => $theme->getId()
        ]);
    }


    #[Route('/theme/{id}/delete', name: 'delete_theme')]
    public function delete(Theme $theme, EntityManagerInterface $entityManager)

    {
        $entityManager->remove($theme);
        $entityManager->flush();

        return $this->redirectToRoute('app_theme');
    }


    #[Route('/theme/{id}', name: 'afficherDetail_theme')]
    public function afficherDetail(Theme $theme): Response
    {
        return $this->render('theme/afficherDetail.html.twig', [
            // on fais passer la variable theme a laquelle on lui donne la valeur theme
            'theme' => $theme
        ]);
    }
}
