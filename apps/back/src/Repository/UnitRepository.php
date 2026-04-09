<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Unit;

/** @extends ServiceEntityRepository<Unit> */
class UnitRepository extends ServiceEntityRepository implements UnitRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Unit::class);
    }

    public function findById(string $id): ?Unit
    {
        return $this->find($id);
    }

    public function save(Unit $unit): void
    {
        $this->getEntityManager()->persist($unit);
        $this->getEntityManager()->flush();
    }

    public function delete(Unit $unit): void
    {
        $this->getEntityManager()->remove($unit);
        $this->getEntityManager()->flush();
    }
}
