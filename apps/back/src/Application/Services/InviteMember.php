<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Entity\PasswordResetToken;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\PasswordResetTokenRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class InviteMember
{
    public function __construct(
        private OrganizationRepositoryInterface $organizationRepository,
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private EmailSenderInterface $emailSender,
        private PasswordResetTokenRepositoryInterface $tokenRepository,
    ) {
    }

    public function execute(
        string $organizationId,
        string $email,
        string $name,
        string $surname,
        string $role = OrganizationMember::ROLE_MEMBER,
    ): OrganizationMember {
        $organization = $this->organizationRepository->findById($organizationId);
        if (null === $organization) {
            throw new \DomainException('Organization not found.');
        }

        if ('' === trim($email) || !filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('A valid email is required.');
        }

        if ('' === trim($name)) {
            throw new \DomainException('Name is required.');
        }

        foreach ($organization->getMembers() as $member) {
            if ($member->getUser()->getEmail() === $email) {
                throw new \DomainException('This user is already a member of the organization.');
            }
        }

        $user = $this->userRepository->findByEmail($email);
        $isNewUser = false;

        if (null === $user) {
            $isNewUser = true;

            $user = new User();
            $user->setName($name);
            $user->setSurname($surname);
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->passwordHasher->hashPassword($user, bin2hex(random_bytes(32))));
            $this->userRepository->save($user);
        }

        $membership = new OrganizationMember();
        $membership->setOrganization($organization);
        $membership->setUser($user);
        $membership->setRole($role);

        $organization->addMember($membership);
        $this->organizationRepository->save($organization);

        if ($isNewUser) {
            try {
                $plainToken = bin2hex(random_bytes(32));
                $hashedToken = hash('sha256', $plainToken);

                $resetToken = new PasswordResetToken();
                $resetToken->setUser($user);
                $resetToken->setToken($hashedToken);
                $resetToken->setExpiresAt(new \DateTimeImmutable('+72 hours', new \DateTimeZone('UTC')));

                $this->tokenRepository->save($resetToken);
                $this->emailSender->sendInvitationEmail($user, $organization, $plainToken);
            } catch (\Throwable) {
                // Email failure should not block invitation
            }
        }

        return $membership;
    }
}
