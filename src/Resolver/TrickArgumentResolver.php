<?php

namespace App\Resolver;

use App\Entity\Trick;
use App\Repository\TrickRepository;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverTrait;

class TrickArgumentResolver implements ArgumentValueResolverInterface
{
    private $trickRepository;

    public function __construct(TrickRepository $trickRepository)
    {
        $this->trickRepository = $trickRepository;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return $argument->getType() === Trick::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $slug = $request->attributes->get('slug');

        if ($slug) {
            $trick = $this->trickRepository->findOneBy(['slug' => $slug]);
            if ($trick) {
                yield $trick;
            }
        }
    }
}
