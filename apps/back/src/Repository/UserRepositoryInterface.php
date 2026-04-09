<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\User;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function findByEmail(string $email): ?User;

    /** @return User[] */
    public function findAll(): array;

    /**
     * @return User[]
     */
    public function findBatch(int $offset, int $limit): array;
}
