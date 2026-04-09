<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Security;

use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, string>
 */
final class OrganizationVoter extends Voter
{
    public const VIEW = 'ORGANIZATION_VIEW';
    public const MANAGE = 'ORGANIZATION_MANAGE';

    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::VIEW, self::MANAGE], true)
            && \is_string($subject);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();
        if (!$user instanceof User) {
            return false;
        }

        /** @var string $organizationId */
        $organizationId = $subject;

        $organization = $this->organizationRepository->findById($organizationId);
        if (null === $organization) {
            return false;
        }

        $membership = $this->findMembership($organization, $user);
        if (null === $membership) {
            return false;
        }

        return match ($attribute) {
            self::VIEW => true,
            self::MANAGE => $membership->isManager(),
            default => false,
        };
    }

    private function findMembership(Organization $organization, User $user): ?OrganizationMember
    {
        foreach ($organization->getMembers() as $member) {
            if ($member->getUser()->getId()->equals($user->getId())) {
                return $member;
            }
        }

        return null;
    }
}
