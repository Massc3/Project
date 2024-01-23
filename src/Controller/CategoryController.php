<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class CategoryController extends AbstractController
{
    #[Route('/admin/category', name: 'app_category')]
    public function index(CategoryRepository $categoryRepository): Response
    {
        // $category = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $categories = $categoryRepository->findBy([], ['nameCategory' => 'ASC']);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('category/index.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'categories' => $categories
        ]);
    }

   
    #[Route('/category/new', name: 'add_category')]
    #[Route('/category/{id}/edit', name: 'edit_category')]
    public function new_edit(Category $category = null, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$category) {
            $category = new Category();
        }


        // on creer le formulaire a partir de categoryType
        $form = $this->createForm(CategoryType::class, $category);
        // on prend en charge la requete demandé
        $form->handleRequest($request);
        // si le formulaire est rempli et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // on recupere les données du formaulaire 
            $category = $form->getData();
            // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
            $entityManager->persist($category);
            // equivalence du execute en PDO
            $entityManager->flush();
            // on fait une redirection vers notre liste category 
            return $this->redirectToRoute('app_category');
        }
        return $this->render('category/new.html.twig', [
            'formAddCategory' => $form,
            'edit' => $category->getId()
        ]);
    }


    #[Route('/category/{id}/delete', name: 'delete_category')]
    public function delete(Category $category, EntityManagerInterface $entityManager)

    {
        $entityManager->remove($category);
        $entityManager->flush();

        return $this->redirectToRoute('app_category');
    }


    #[Route('/category/{id}', name: 'afficherDetail_category')]
    public function afficherDetail(Category $category): Response
    {
        return $this->render('category/afficherDetail.html.twig', [
            // on fais passer la variable category a laquelle on lui donne la valeur category
            'category' => $category
        ]);
    }

    
}
