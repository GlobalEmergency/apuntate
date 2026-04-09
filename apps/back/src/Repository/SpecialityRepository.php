<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Speciality;

/** @extends ServiceEntityRepository<Speciality> */
class SpecialityRepository extends ServiceEntityRepository implements SpecialityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Speciality::class);
    }

    public function findById(string $id): ?Speciality
    {
        return $this->find($id);
    }

    public function save(Speciality $speciality): void
    {
        $this->getEntityManager()->persist($speciality);
        $this->getEntityManager()->flush();
    }

    public function delete(Speciality $speciality): void
    {
        $this->getEntityManager()->remove($speciality);
        $this->getEntityManager()->flush();
    }
}
