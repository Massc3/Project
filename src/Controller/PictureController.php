<?php

namespace App\Controller;

use App\Entity\Picture;
use App\Form\PictureType;
use App\Repository\PictureRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class PictureController extends AbstractController
{
    // #[Route('/picture', name: 'app_picture')]
    // public function index(): Response
    // {
    //     $picture = new Picture();
    //     $form = $this->createForm(PictureType::class, $picture);
    //     $form->handleRequest($request);

    //     if ($form->isSubmitted() && $form->isValid()) {
    //         /** @var UploadedFile $brochureFile */
    //         $brochureFile = $form->get('brochure')->getData();

    //         // this condition is needed because the 'brochure' field is not required
    //         // so the PDF file must be processed only when a file is uploaded
    //         if ($brochureFile) {
    //             $originalFilename = pathinfo($brochureFile->getClientOriginalName(), PATHINFO_FILENAME);
    //             // this is needed to safely include the file name as part of the URL
    //             $safeFilename = $slugger->slug($originalFilename);
    //             $newFilename = $safeFilename . '-' . uniqid() . '.' . $brochureFile->guessExtension();

    //             // Move the file to the directory where brochures are stored
    //             try {
    //                 $brochureFile->move(
    //                     $this->getParameter('brochures_directory'),
    //                     $newFilename
    //                 );
    //             } catch (FileException $e) {
    //                 // ... handle exception if something happens during file upload
    //             }

    //             // updates the 'brochureFilename' property to store the PDF file name
    //             // instead of its contents
    //             $picture->setBrochureFilename($newFilename);
    //         }

    //         // ... persist the $picture variable or any other work

    //         return $this->redirectToRoute('app_picture_list');
    //     }

    //     return $this->render('picture/new.html.twig', [
    //         'form' => $form,
    //     ]);
    // }

    #[Route('/picture', name: 'app_picture')]
    public function index(PictureRepository $pictureRepository): Response
    {
        // $picture = $employRepository->findAll();
        // SELECT * FROM empoye ORDER BY nom ASC;
        $picture = $pictureRepository->findBy([], ['title' => 'ASC']);
        // on les envoient grace a la methode render a la view index.html.twig
        return $this->render('picture/index.html.twig', [
            // on fais passer la variable entreprise a laquelle on lui donne la valeur entreprise
            'picture' => $picture
        ]);
    }
    #[Route('/picture/new', name: 'add_picture')]
    #[Route('/picture/{id}/edit', name: 'edit_picture')]
    public function new_edit(Picture $picture = null, Request $request, EntityManagerInterface $entityManager): Response
    {

        if (!$picture) {
            $picture = new Picture();
        }


        // on creer le formulaire a partir de pictureType
        $form = $this->createForm(PictureType::class, $picture);
        // on prend en charge la requete demandé
        $form->handleRequest($request);
        // si le formulaire est rempli et qu'il est valide
        if ($form->isSubmitted() && $form->isValid()) {
            // on recupere les données du formaulaire 
            $picture = $form->getData();
            // equivalence du prepare en PDO, on prepare l'object qui va etre en base de données
            $entityManager->persist($picture);
            // equivalence du execute en PDO
            $entityManager->flush();
            // on fait une redirection vers notre liste picture 
            return $this->redirectToRoute('app_picture');
        }
        return $this->render('picture/new.html.twig', [
            'formAddPicture' => $form,
            'edit' => $picture->getId()
        ]);
    }
    #[Route('/picture/{id}/delete', name: 'delete_picture')]
    public function delete(Picture $picture, EntityManagerInterface $entityManager)

    {
        $entityManager->remove($picture);
        $entityManager->flush();

        return $this->redirectToRoute('app_picture');
    }


    #[Route('/picture/{id}', name: 'afficherDetail_picture')]
    public function afficherDetail(Picture $picture): Response
    {
        return $this->render('picture/afficherDetail.html.twig', [
            // on fais passer la variable picture a laquelle on lui donne la valeur picture
            'picture' => $picture
        ]);
    }
}
