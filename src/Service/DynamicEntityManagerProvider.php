<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class DynamicEntityManagerProvider
{
    private ?EntityManagerInterface $entityManager = null;

    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    public function getEntityManager(): ?EntityManagerInterface
    {
        return $this->entityManager;
    }
}
