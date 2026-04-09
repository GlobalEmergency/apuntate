<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\UnitComponent;

/** @extends ServiceEntityRepository<UnitComponent> */
class UnitComponentRepository extends ServiceEntityRepository implements UnitComponentRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UnitComponent::class);
    }

    public function findById(string $id): ?UnitComponent
    {
        return $this->find($id);
    }

    public function save(UnitComponent $unitComponent): void
    {
        $this->getEntityManager()->persist($unitComponent);
        $this->getEntityManager()->flush();
    }

    public function delete(UnitComponent $unitComponent): void
    {
        $this->getEntityManager()->remove($unitComponent);
        $this->getEntityManager()->flush();
    }
}
