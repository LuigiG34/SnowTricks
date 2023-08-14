<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\TrickRepository;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;

class HomepageController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(TrickRepository $repository): Response
    {
        $paginator = $repository->getTrickPaginator(0);

        return $this->render('homepage/index.html.twig', [
            'tricks' => $paginator,
        ]);
    }


    #[Route('/get-more-tricks/{offset}', name: 'get_more_tricks', methods: ['GET'])]
    public function getMoreTricks(TrickRepository $repository, $offset, TokenGeneratorInterface $tokenGenerator)
    {
        $paginator = $repository->getTrickPaginator($offset);
        $html="";

        foreach ($paginator as $trick) {
            $images = $trick->getImages();

            if (!empty($images)) {
                foreach($images as $img){
                    if($img->getIsMain()){
                        $mainImg = $img->getName();
                    }
                }
            } else {
                $mainImg = "/assets/img/default.jpg";
            }
            
            $html .= "
            <div class='col-s-12 col-m-6 col-lg-4 trick'>
                <div class='card bg-light shadow m-5'>
                    <img src='/uploads/".$mainImg."' class='card-img-top img-trick' alt='...'>
                    <div class='card-body'>
                        <h5 class='card-title'>
                            <div class='d-flex justify-content-between align-items-center'>
                                <a class='btn btn-primary' href='/tricks/".$trick->getSlug()."'>
                                    ".$trick->getName()."</a>";
            
            if($this->getUser()) {
                $html .= "<div>
                <a class='btn btn-light' href='/tricks/edit/".$trick->getSlug()."'>
                    <i class='bi bi-pencil-fill'></i>
                </a>
                <button type='button' class='btn btn-light' data-bs-toggle='modal' data-bs-target='#exampleModal".$trick->getId()."'>
                    <i class='bi bi-trash-fill'></i>
                </button>
                </div>
                <div class='modal fade' id='exampleModal".$trick->getId()."' tabindex='-1' aria-labelledby='exampleModalLabel' aria-hidden='true'>
                    <div class='modal-dialog'>
                        <div class='modal-content'>
                            <div class='modal-header'>
                                <h5 class='modal-title' id='exampleModalLabel'>Delete '".$trick->getName()."'
                                </h5>
                                <button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>
                            </div>
                            <div class='modal-body'>
                                Are you sure you want to delete '".$trick->getName()."' ?
                            </div>
                            <div class='modal-footer'>
                                <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Cancel</button>
                                <form method='post' action='/tricks/delete/".$trick->getSlug()."'>
                                    <input type='hidden' name='_method' value='DELETE'>
                                    <input type='hidden' name='_token' value='".$tokenGenerator->generateToken("delete".$trick->getSlug())."'>
                                    <button class='btn btn-danger'>Delete</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>";
            }

            $html .= "
                            </div>
                        </h5>
                    </div>
                </div>
            </div>";                            
        }

        return new Response($html);
    }
}




