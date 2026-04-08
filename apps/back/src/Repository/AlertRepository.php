<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Alert;
use GlobalEmergency\Apuntate\Entity\User;

class AlertRepository extends ServiceEntityRepository implements AlertRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Alert::class);
    }

    public function save(Alert $alert): void
    {
        $this->getEntityManager()->persist($alert);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?Alert
    {
        return $this->find($id);
    }

    public function findUnreadByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.recipient = :user')
            ->andWhere('a.read = false')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findByUser(User $user): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.recipient = :user')
            ->setParameter('user', $user)
            ->orderBy('a.createdAt', 'DESC')
            ->setMaxResults(50)
            ->getQuery()
            ->getResult();
    }
}
