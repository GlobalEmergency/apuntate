<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;

final class ChangeMemberRole
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    public function execute(string $organizationId, string $userId, string $newRole): OrganizationMember
    {
        $organization = $this->organizationRepository->findById($organizationId);
        if (null === $organization) {
            throw new \DomainException('Organization not found.');
        }

        $targetMember = null;
        $adminCount = 0;

        foreach ($organization->getMembers() as $member) {
            if ($member->isAdmin()) {
                ++$adminCount;
            }
            if ($member->getUser()->getId()->toRfc4122() === $userId) {
                $targetMember = $member;
            }
        }

        if (null === $targetMember) {
            throw new \DomainException('User is not a member of this organization.');
        }

        if ($targetMember->isAdmin() && OrganizationMember::ROLE_ADMIN !== $newRole && $adminCount <= 1) {
            throw new \DomainException('Cannot demote the last admin of the organization.');
        }

        $targetMember->setRole($newRole);
        $this->organizationRepository->save($organization);

        return $targetMember;
    }
}
