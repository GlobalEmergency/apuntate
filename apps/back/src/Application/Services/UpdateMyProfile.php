<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;

final class UpdateMyProfile
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function execute(User $user, ?string $name = null, ?string $surname = null): User
    {
        if (null !== $name) {
            if ('' === trim($name)) {
                throw new \DomainException('Name cannot be empty.');
            }
            $user->setName($name);
        }

        if (null !== $surname) {
            $user->setSurname($surname);
        }

        $this->userRepository->save($user);

        return $user;
    }
}
