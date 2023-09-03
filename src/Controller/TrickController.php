<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Image;
use App\Entity\Trick;
use App\Entity\Video;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CommentType;
use App\Form\TrickType;
use App\Repository\CommentRepository;
use App\Repository\ImageRepository;
use App\Repository\TrickRepository;
use App\Service\TrickService;

#[Route('/tricks', name: 'trick_', methods: ['GET', 'POST'])]
class TrickController extends AbstractController
{

    #[Route('/add', name: 'add', methods: ['GET', 'POST'])]
    public function addTrick(TrickRepository $repository, TrickService $trickService, Request $request): Response
    {
        $trick = new Trick;
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                // On traite l'image principale, les images secondaires et l'url de la vidéo YouTube
                $trickService->processMedia($trick, $this->getParameter('images_directory'), $form->get('mainImageFile')->getData(), $form->get('multiple_images')->getData(), $form->get('video_url')->getData());

                // On ajoute l'auteur
                $trick->setUser($this->getUser());
                // On enregistre le trick
                $repository->save($trick, true);

                $this->addFlash('success', 'Trick added successfully !');
                return $this->redirectToRoute('trick_show', [
                    'slug' => $trick->getSlug()
                ]);

            } else {

                $errors = $form->getErrors(true, true);

                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
                return $this->redirectToRoute('trick_add');
            }
        }

        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
    }

    #[Route('/{slug}', name: 'show', methods: ['GET', 'POST'])]
    public function index(Trick $trick, Request $request, CommentRepository $commentRepository): Response
    {
        $comment = new Comment;
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment->setContent($comment->getContent());
                $comment->setTrick($trick);
                $comment->setUser($this->getUser());
                $commentRepository->save($comment, true);

                $this->addFlash('success', 'Comment added successfully !');
            } else {
                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }
            return $this->redirectToRoute('app_trick', ['slug' => $trick->getSlug()]);
        }

        $comments = $commentRepository->getCommentPaginator($trick, 0);

        return $this->render('trick/index.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick,
            'comments' => $comments
        ]);
    }

    #[Route('/{slug}/get-more-comments/{offset}', name: 'get_more_comments', methods: ['GET'])]
    public function getMoreTricks(CommentRepository $repository, $offset, Trick $trick)
    {
        $paginator = $repository->getCommentPaginator($trick, $offset);
        $html="";

        foreach ($paginator as $comment) {
            
            $html .= "
            <div class='col comment'>
						<div class='d-flex align-items-center justify-content-start'>
							<div>
								<img class='user-img' src='".$comment->getUser()->getImage()."'>
							</div>
							<div class='m-3 p-3 border w-100 rounded'>
								<small>
									<strong>".$comment->getUser()->getUsername()."</strong>
									|
									".$comment->getCreatedAt()->format('d-m-Y')."</small>
								<p>".$comment->getContent()."</p>
							</div>
						</div>
					</div>";
        }

        return new Response($html);
    }

    #[Route('/edit/{slug}', name: 'edit', methods: ['GET', 'POST'])]
    public function updateTrick(Trick $trick, TrickRepository $repository, TrickService $trickService, ImageRepository $imageRepository, Request $request): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            
            if ($form->isValid()) {
                
                // Remplacer l'image princiapl actuel si elle existe
                $trickService->updateMainImage($trick, $form->get('mainImageFile')->getData(), $this->getParameter('images_directory'), $imageRepository);
                // On traite l'image principale, les images secondaires et l'url de la vidéo YouTube
                $trickService->processMedia($trick, $this->getParameter('images_directory'), $form->get('mainImageFile')->getData(), $form->get('multiple_images')->getData(), $form->get('video_url')->getData());

                // On ajoute l'auteur
                $trick->setUser($this->getUser());
                // On enregistre le trick
                $repository->save($trick, true);

                $this->addFlash('success', 'Trick updated successfully !');
            } else {

                $errors = $form->getErrors(true, true);
                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
            }

            return $this->redirectToRoute('trick_edit', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/update.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }

    #[Route('/delete/{slug}', name: 'delete')]
    public function deleteTrick(Trick $trick, Request $request, TrickRepository $trickRepository): Response
    {

        if ($this->isCsrfTokenValid('delete' . $trick->getSlug(), $request->request->get('_token'))) {

            $images = $trick->getImages();

            foreach ($images as $image) {
                $nameImageBool = file_exists($this->getParameter('images_directory') . $image->getName());

                if ($nameImageBool !== false) {
                    unlink($this->getParameter('images_directory') . $image->getName());
                }
            }

            $trickRepository->remove($trick, true);
        }

        $this->addFlash('success', 'Trick deleted successfully.');
        return $this->redirectToRoute('app_homepage');
    }
}
