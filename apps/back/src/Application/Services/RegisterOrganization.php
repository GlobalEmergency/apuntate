<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class RegisterOrganization
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {}

    public function execute(string $name, string $email, string $plainPassword): User
    {
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser !== null) {
            throw new \DomainException('A user with this email already exists.');
        }

        $user = new User();
        $user->setName($name);
        $user->setSurname('');
        $user->setEmail($email);
        $user->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user);

        return $user;
    }
}
