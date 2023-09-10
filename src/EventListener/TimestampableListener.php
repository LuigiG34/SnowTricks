<?php

namespace App\EventListener;

use App\Model\TimestampInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

class TimestampableListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $entity = $event->getObject();
        if($entity instanceof TimestampInterface) {
            $entity->setCreatedAt(new \DateTimeImmutable());
        }
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $entity = $event->getObject();
        if($entity instanceof TimestampInterface) {
            $entity->setUpdatedAt(new \DateTimeImmutable());
        }
    }
}
