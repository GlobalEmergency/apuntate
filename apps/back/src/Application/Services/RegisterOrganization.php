<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterOrganization
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private OrganizationRepositoryInterface $organizationRepository,
        private EmailSenderInterface $emailSender,
    ) {
    }

    /** @return array{user: User, organization: Organization} */
    public function execute(string $orgName, string $name, string $email, string $plainPassword): array
    {
        if ('' === trim($orgName)) {
            throw new \DomainException('Organization name is required.');
        }

        if ('' === trim($name)) {
            throw new \DomainException('Name is required.');
        }

        if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
            throw new \DomainException('Invalid email format.');
        }

        if (\strlen($plainPassword) < 6) {
            throw new \DomainException('Password must be at least 6 characters.');
        }

        $existingUser = $this->userRepository->findByEmail($email);
        if (null !== $existingUser) {
            throw new \DomainException('A user with this email already exists.');
        }

        $organization = new Organization();
        $organization->setName($orgName);
        $organization->setSlug($orgName);

        $user = new User();
        $user->setName($name);
        $user->setSurname('');
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $membership = new OrganizationMember();
        $membership->setUser($user);
        $membership->setOrganization($organization);
        $membership->setRole(OrganizationMember::ROLE_ADMIN);
        $organization->addMember($membership);

        $this->userRepository->save($user);
        $this->organizationRepository->save($organization);

        try {
            $this->emailSender->sendWelcomeEmail($user, $organization);
        } catch (\Throwable) {
            // Email failure should not block registration
        }

        return [
            'user' => $user,
            'organization' => $organization,
        ];
    }
}
