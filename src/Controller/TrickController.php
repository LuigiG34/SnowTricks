<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Trick;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Repository\VideoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class TrickController extends AbstractController
{

    #[Route('/tricks/{slug}', name: 'app_trick')]
    public function index(Trick $trick, Request $request, EntityManagerInterface $manager): Response
    {
        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        // Ajouter validation des données

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setContent($comment->getContent());
            $comment->setTrick($trick);
            $comment->setUser($this->getUser());
            $manager->persist($comment);
            $manager->flush();

            $this->addFlash('success', 'Comment added successfully !');
            return $this->redirectToRoute('app_trick', ['slug' => $trick->getSlug()]);
        }

        return $this->render('trick/index.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
        ]);
    }

    // Ajouter les commentaires en AJAX


    // à modifier
    #[Route('/get-more-tricks/{offset}/{limit}', name: 'get_more_tricks', methods: ['GET'])]
    public function getMoreTricks(TrickRepository $repository, $offset, $limit): JsonResponse
    {
        // MODIFIER : Créer un parser -> prendre des tricks et l'afficher en HTML
        // MODIFIER : Mettre offset et limit dans la requete SQL directement 
        // MODIFIER : Retourne du HTML au lieu de JSON

        $all = $repository->findAll();

        $tricksData = [];
        foreach ($all as $trick) {
            $firstImage = null;
            $images = $trick->getImages();
            if (!empty($images)) {
                $firstImage = $images[0]->getPath();
            } else {
                $firstImage = "/assets/img/default.jpg";
            }

            $tricksData[] = [
                'name' => $trick->getName(),
                'slug' => $trick->getSlug(),
                'image' => $firstImage,
            ];
        }

        $moreTricks = array_slice($tricksData, $offset, $limit);

        return new JsonResponse($moreTricks);
    }





    #[Route('/tricks/edit/{slug}', name: 'app_edit_trick')]
    public function updateTrick(Trick $trick, TrickRepository $repository, Request $request): Response
    {
        // Ajouter validation sur les champs
        // Ajouter video et image directement dans le formulaire 

        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository->save($trick);

            $this->addFlash('success', 'Trick updated successfully !');
            return $this->redirectToRoute('app_trick', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/update.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }







    #[Route('/tricks/delete/{slug}', name: 'app_delete_trick')]
    public function deleteTrick(Trick $trick, Request $request, EntityManagerInterface $manager, ImageRepository $imageRepository, CommentRepository $commentRepository, VideoRepository $videoRepository): Response
    {
        // UTILISER SLUF A LA PLACE D'ID
        // METTRE trick a la place de $id dans les paramètre de la méthode 

        // Suppression en CASCADE pour éléments associés à l'entité Trick (video, image, comment)

        if ($this->isCsrfTokenValid('delete' . $trick->getSlug(), $request->request->get('_token'))) {

            $images = $imageRepository->findBy(["trick" => $trick]);

            foreach ($images as $image) {
                $nameImageBool = file_exists($this->getParameter('public_directory').$image->getPath());

                if ($nameImageBool !== false) {
                    unlink($this->getParameter('public_directory').$image->getPath());
                }
                $manager->remove($image);
            }

            $videos = $videoRepository->findBy(["trick" => $trick]);
            foreach ($videos as $video) {
                $manager->remove($video);
            }

            $comments = $commentRepository->findBy(["trick" => $trick]);
            foreach ($comments as $comment) {
                $manager->remove($comment);
            }

            $manager->remove($trick);
            $manager->flush();
        }

        $this->addFlash('success', 'Trick deleted successfully.');
        return $this->redirectToRoute('app_homepage');
    }
}
