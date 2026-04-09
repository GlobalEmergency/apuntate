<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Component;

class ComponentRepository extends ServiceEntityRepository implements ComponentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Component::class);
    }

    public function findById(string $id): ?Component
    {
        return $this->find($id);
    }

    public function save(Component $component): void
    {
        $this->getEntityManager()->persist($component);
        $this->getEntityManager()->flush();
    }

    public function delete(Component $component): void
    {
        $this->getEntityManager()->remove($component);
        $this->getEntityManager()->flush();
    }
}
