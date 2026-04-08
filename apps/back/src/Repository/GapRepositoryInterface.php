<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Gap;

interface GapRepositoryInterface
{
    public function save(Gap $gap): void;

    public function findById(string $id): ?Gap;

    public function findAvailableByService(string $serviceId): array;

    public function findByService(string $serviceId): array;
}
