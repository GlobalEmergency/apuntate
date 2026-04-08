<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Alert;
use GlobalEmergency\Apuntate\Entity\User;

interface AlertRepositoryInterface
{
    public function save(Alert $alert): void;

    public function findById(string $id): ?Alert;

    public function findUnreadByUser(User $user): array;

    public function findByUser(User $user): array;
}
