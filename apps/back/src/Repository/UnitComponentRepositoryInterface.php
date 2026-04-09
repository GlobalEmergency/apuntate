<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\UnitComponent;

interface UnitComponentRepositoryInterface
{
    public function findById(string $id): ?UnitComponent;

    public function save(UnitComponent $unitComponent): void;

    public function delete(UnitComponent $unitComponent): void;
}
