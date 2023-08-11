<?php

namespace App\EventListener;

use App\Model\SlugInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

class SluggableListener implements EventSubscriber
{
    public function __construct(private SluggerInterface $slugger)
    {

    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(PrePersistEventArgs $event)
    {
        $entity = $event->getObject();
        if($entity instanceof SlugInterface) {
            $entity->setSlug(strtolower($this->slugger->slug((string) $entity)));
        }
    }

    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getObject();
        if($entity instanceof SlugInterface) {
            $entity->setSlug(strtolower($this->slugger->slug((string) $entity)));
        }
    }
}
