<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\User;

interface GapRepositoryInterface
{
    public function save(Gap $gap): void;

    public function findById(string $id): ?Gap;

    public function findByIdForUpdate(string $id): ?Gap;

    public function findAvailableByService(string $serviceId): array;

    public function findByService(string $serviceId): array;

    /** @return Gap[] */
    public function findCompletedByUser(User $user): array;
}
