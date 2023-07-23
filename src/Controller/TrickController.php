<?php

namespace App\Controller;

use App\Entity\Comment;
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
    public function index(string $slug, TrickRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        $unique = $repository->findOneBy(['slug' => $slug]);
        $user = $this->getUser();

        $form = $this->createForm(CommentType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $comment = new Comment;
            $comment->setContent($data->getContent());
            $comment->setTrick($unique);
            $comment->setUser($user);
            $manager->persist($comment);
            $manager->flush();

            return $this->redirectToRoute('app_trick', ['slug' => $unique->getSlug()]);
        }

        return $this->render('trick/index.html.twig', [
            'form' => $form->createView(),
            'trick' => $unique,
        ]);
    }

    #[Route('/get-more-tricks/{offset}/{limit}', name: 'get_more_tricks', methods: ['GET'])]
    public function getMoreTricks(TrickRepository $repository, $offset, $limit): JsonResponse
    {
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
    public function updateTrick(string $slug, TrickRepository $repository, Request $request, EntityManagerInterface $manager): Response
    {
        $unique = $repository->findOneBy(['slug' => $slug]);
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'You need to login to modify a trick.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getId() !== $unique->getUser()->getId()) {
            $this->addFlash('danger', 'You don\'t have access to this trick.');
            return $this->redirectToRoute('app_homepage');
        }

        $form = $this->createForm(TrickType::class, $unique);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager->flush();

            $this->addFlash('success', 'Trick updated successfully !');
            return $this->redirectToRoute('app_trick', [
                'slug' => $slug
            ]);
        }

        return $this->render('trick/update.html.twig', [
            'form' => $form->createView(),
            'trick' => $unique
        ]);
    }

    #[Route('/tricks/delete/{id}', name: 'app_delete_trick')]
    public function deleteTrick(string $id, TrickRepository $repository, Request $request, EntityManagerInterface $manager, ImageRepository $imageRepository, CommentRepository $commentRepository, VideoRepository $videoRepository): Response
    {
        $trick = $repository->findOneBy(['id' => $id]);
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('danger', 'You need to login to modify a trick.');
            return $this->redirectToRoute('app_login');
        }

        if ($user->getId() !== $trick->getUser()->getId()) {
            $this->addFlash('danger', 'You don\'t have access to this trick.');
            return $this->redirectToRoute('app_homepage');
        }

        if ($this->isCsrfTokenValid('delete' . $trick->getId(), $request->request->get('_token'))) {

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
