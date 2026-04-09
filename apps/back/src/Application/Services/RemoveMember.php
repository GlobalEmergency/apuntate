<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;

final class RemoveMember
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function execute(string $organizationId, string $userId): void
    {
        $organization = $this->organizationRepository->findById($organizationId);
        if (null === $organization) {
            throw new \DomainException('Organization not found.');
        }

        $memberToRemove = null;
        foreach ($organization->getMembers() as $member) {
            if ($member->getUser()->getId()->toRfc4122() === $userId) {
                $memberToRemove = $member;
                break;
            }
        }

        if (null === $memberToRemove) {
            throw new \DomainException('User is not a member of this organization.');
        }

        $adminCount = 0;
        foreach ($organization->getMembers() as $member) {
            if ($member->isAdmin()) {
                ++$adminCount;
            }
        }

        if ($memberToRemove->isAdmin() && $adminCount <= 1) {
            throw new \DomainException('Cannot remove the last admin of the organization.');
        }

        $organization->getMembers()->removeElement($memberToRemove);
        $this->organizationRepository->save($organization);
    }
}
