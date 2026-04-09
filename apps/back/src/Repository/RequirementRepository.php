<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Requirement;

/** @extends ServiceEntityRepository<Requirement> */
class RequirementRepository extends ServiceEntityRepository implements RequirementRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Requirement::class);
    }

    public function save(Requirement $requirement): void
    {
        $this->getEntityManager()->persist($requirement);
        $this->getEntityManager()->flush();
    }

    public function delete(Requirement $requirement): void
    {
        $this->getEntityManager()->remove($requirement);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?Requirement
    {
        return $this->find($id);
    }

    /** @return Requirement[] */
    public function findAll(): array
    {
        return parent::findAll();
    }
}
