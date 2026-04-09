<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\Organization;

/** @extends ServiceEntityRepository<Organization> */
class OrganizationRepository extends ServiceEntityRepository implements OrganizationRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function save(Organization $organization): void
    {
        $this->getEntityManager()->persist($organization);
        $this->getEntityManager()->flush();
    }

    public function findById(string $id): ?Organization
    {
        return $this->find($id);
    }

    public function findBySlug(string $slug): ?Organization
    {
        return $this->findOneBy(['slug' => $slug]);
    }
}
