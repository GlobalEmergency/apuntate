<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\User;

/**
 * @method Gap|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gap|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gap[]    findAll()
 * @method Gap[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GapRepository extends ServiceEntityRepository implements GapRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gap::class);
    }

    public function findById(string $id): ?Gap
    {
        return $this->find($id);
    }

    public function findByIdForUpdate(string $id): ?Gap
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->setLockMode(\Doctrine\DBAL\LockMode::PESSIMISTIC_WRITE)
            ->getOneOrNullResult();
    }

    public function save(Gap $gap): void
    {
        $this->getEntityManager()->persist($gap);
        $this->getEntityManager()->flush();
    }

    public function findAvailableByService(string $serviceId): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.service = :serviceId')
            ->andWhere('g.user IS NULL')
            ->setParameter('serviceId', $serviceId)
            ->getQuery()
            ->getResult();
    }

    public function findByService(string $serviceId): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.service = :serviceId')
            ->setParameter('serviceId', $serviceId)
            ->leftJoin('g.user', 'u')
            ->addSelect('u')
            ->leftJoin('g.unitComponent', 'uc')
            ->addSelect('uc')
            ->leftJoin('uc.component', 'c')
            ->addSelect('c')
            ->getQuery()
            ->getResult();
    }

    public function findCompletedByUser(User $user): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.user = :user')
            ->setParameter('user', $user)
            ->leftJoin('g.service', 's')
            ->addSelect('s')
            ->orderBy('s.dateStart', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function delete(Gap $gap): void
    {
        $this->getEntityManager()->remove($gap);
        $this->getEntityManager()->flush();
    }
}
