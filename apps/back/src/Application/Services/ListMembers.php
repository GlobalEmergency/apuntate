<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;

final class ListMembers
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    /** @return OrganizationMember[] */
    public function execute(string $organizationId): array
    {
        $organization = $this->organizationRepository->findById($organizationId);
        if (null === $organization) {
            throw new \DomainException('Organization not found.');
        }

        return $organization->getMembers()->toArray();
    }
}
