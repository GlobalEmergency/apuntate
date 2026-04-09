<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Alert;
use GlobalEmergency\Apuntate\Entity\User;

interface AlertRepositoryInterface
{
    public function save(Alert $alert): void;

    /** @param Alert[] $alerts */
    public function saveAll(array $alerts): void;

    public function findById(string $id): ?Alert;

    /** @return Alert[] */
    public function findUnreadByUser(User $user): array;

    /** @return Alert[] */
    public function findByUser(User $user): array;
}
