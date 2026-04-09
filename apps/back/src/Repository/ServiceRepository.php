<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Carbon\Carbon;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Service;

/**
 * @extends ServiceEntityRepository<Service>
 *
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository implements ServiceRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function save(Service $service): void
    {
        $this->getEntityManager()->persist($service);
        $this->getEntityManager()->flush();
    }

    public function delete(Service $service): void
    {
        $this->getEntityManager()->remove($service);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?Service
    {
        return $this->find($id);
    }

    /** @return Service[] */
    public function findUpcoming(): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateStart > :now')
            ->setParameter('now', Carbon::now())
            ->orderBy('s.dateStart', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /** @return Service[] */
    public function findBetweenDates(\DateTimeInterface $start, \DateTimeInterface $end): array
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.dateStart BETWEEN :start AND :end')
            ->setParameter('start', $start->format('Y-m-d H:i'))
            ->setParameter('end', $end->format('Y-m-d H:i'))
            ->orderBy('s.dateStart', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
