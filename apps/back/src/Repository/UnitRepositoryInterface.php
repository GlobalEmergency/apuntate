<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Unit;

interface UnitRepositoryInterface
{
    public function findById(string $id): ?Unit;

    /** @return Unit[] */
    public function findAll(): array;
}
