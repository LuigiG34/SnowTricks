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
use App\Repository\VideoRepository;


class TrickController extends AbstractController
{

    #[Route('/tricks/{slug}', name: 'app_trick', methods: ['GET', 'POST'])]
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



    #[Route('/tricks/{slug}/get-more-comments/{offset}', name: 'get_more_comments', methods: ['GET'])]
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


    #[Route('/add/trick', name: 'app_add_trick', methods: ['GET', 'POST'])]
    public function addTrick(TrickRepository $repository, Request $request): Response
    {
        $trick = new Trick;
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                // Traitement de l'image principale
                if ($form->get('mainImageFile')->getData() !== null) {
                    $mainImg = $form->get('mainImageFile')->getData();
                    $fileMainImg = md5(uniqid()) . '.' . $mainImg->guessExtension();
                    $mainImg->move($this->getParameter('images_directory'), $fileMainImg);
                    $mainImageEntity = new Image;
                    $mainImageEntity->setName($fileMainImg);
                    $mainImageEntity->setIsMain(true);
                    $trick->addImage($mainImageEntity);
                }

                // Traitement des images secondaires
                if ($form->get('multiple_images')->getData() !== null) {
                    $images = $form->get('multiple_images')->getData();
                    foreach ($images as $image) {
                        $file = md5(uniqid()) . '.' . $image->guessExtension();
                        $image->move($this->getParameter('images_directory'), $file);

                        $imageEntity = new Image;
                        $imageEntity->setName($file);
                        $imageEntity->setIsMain(false);
                        $trick->addImage($imageEntity);
                    }
                }

                // On ajoute la vidéo
                if ($form->get('video_url')->getData() !== null) {
                    $videoUrl = str_replace("watch?v=", "embed/", $form->get('video_url')->getData());
                    $videoEntity = new Video;
                    $videoEntity->setLink($videoUrl);
                    $trick->addVideo($videoEntity);
                }

                // On ajoute l'auteur
                $trick->setUser($this->getUser());
                // On enregistre le trick
                $repository->save($trick, true);

                $this->addFlash('success', 'Trick added successfully !');
                return $this->redirectToRoute('app_trick', [
                    'slug' => $trick->getSlug()
                ]);
            } else {

                $errors = $form->getErrors(true, true);

                foreach ($errors as $error) {
                    $this->addFlash('danger', $error->getMessage());
                }
                return $this->redirectToRoute('app_add_trick');
            }
        }

        return $this->render('trick/add.html.twig', [
            'form' => $form->createView()
        ]);
    }


    #[Route('/tricks/edit/{slug}', name: 'app_edit_trick', methods: ['GET', 'POST'])]
    public function updateTrick(Trick $trick, TrickRepository $repository, Request $request): Response
    {
        $form = $this->createForm(TrickType::class, $trick);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {

                // Traitement de l'image principale
                if ($form->get('mainImageFile')->getData() !== null) {

                    // On supprimer l'image principale actuelle
                    $imageCollection = $trick->getImages();
                    if (!empty($imageCollection)) {
                        foreach ($imageCollection as $img) {
                            if ($img->getIsMain()) {
                                $nameImageBool = file_exists($this->getParameter('images_directory') . $img->getName());

                                if ($nameImageBool !== false) {
                                    unlink($this->getParameter('images_directory') . $img->getName());
                                }

                                $trick->removeImage($img);
                            }
                        }
                    }

                    // On ajoute la nouvelle image
                    $mainImg = $form->get('mainImageFile')->getData();
                    $fileMainImg = md5(uniqid()) . '.' . $mainImg->guessExtension();
                    $mainImg->move($this->getParameter('images_directory'), $fileMainImg);
                    $mainImageEntity = new Image;
                    $mainImageEntity->setName($fileMainImg);
                    $mainImageEntity->setIsMain(true);
                    $trick->addImage($mainImageEntity);
                }

                // Traitement des images secondaires
                if ($form->get('multiple_images')->getData() !== null) {
                    $images = $form->get('multiple_images')->getData();
                    foreach ($images as $image) {
                        $file = md5(uniqid()) . '.' . $image->guessExtension();
                        $image->move($this->getParameter('images_directory'), $file);

                        $imageEntity = new Image;
                        $imageEntity->setName($file);
                        $imageEntity->setIsMain(false);
                        $trick->addImage($imageEntity);
                    }
                }

                // On ajoute la vidéo
                if ($form->get('video_url')->getData() !== null) {
                    $videoUrl = str_replace("watch?v=", "embed/", $form->get('video_url')->getData());
                    $videoEntity = new Video;
                    $videoEntity->setLink($videoUrl);
                    $trick->addVideo($videoEntity);
                }

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

            return $this->redirectToRoute('app_edit_trick', [
                'slug' => $trick->getSlug()
            ]);
        }

        return $this->render('trick/update.html.twig', [
            'form' => $form->createView(),
            'trick' => $trick
        ]);
    }

    #[Route('/tricks/delete/{slug}', name: 'app_delete_trick')]
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

    #[Route('/image/delete/{id}', name: 'app_delete_image')]
    public function deleteImage($id, Request $request, ImageRepository $imageRepository): Response
    {
        $image = $imageRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {

            $nameImageBool = file_exists($this->getParameter('images_directory') . $image->getName());

            if ($nameImageBool !== false) {
                unlink($this->getParameter('images_directory') . $image->getName());
            }

            $imageRepository->remove($image, true);
        }

        $this->addFlash('success', 'Image deleted successfully.');
        return $this->redirectToRoute('app_trick', ['slug' => $image->getTrick()->getSlug()]);
    }

    #[Route('/video/delete/{id}', name: 'app_delete_video')]
    public function deleteVideo($id, Request $request, VideoRepository $videoRepository): Response
    {
        $video = $videoRepository->find($id);

        if ($this->isCsrfTokenValid('delete' . $video->getId(), $request->request->get('_token'))) {

            $videoRepository->remove($video, true);
        }

        $this->addFlash('success', 'Video deleted successfully.');
        return $this->redirectToRoute('app_trick', ['slug' => $video->getTrick()->getSlug()]);
    }
}
