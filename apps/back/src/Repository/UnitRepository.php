<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Unit;

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
}
