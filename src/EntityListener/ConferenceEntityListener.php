<?php

namespace App\EntityListener;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;
use Symfony\Component\String\Slugger\SluggerInterface;

#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
#[AsDoctrineListener(event: Events::prePersist, priority: 500, connection: 'default')]
class ConferenceEntityListener
{
    private SluggerInterface $slugger;

    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    public function prePersist(PrePersistEventArgs $event): void
    {
        $event->getObject()->computeSlug($this->slugger);
    }

    public function preUpdate(PreUpdateEventArgs $event): void
    {
        $event->getObject()->computeSlug($this->slugger);
    }
}