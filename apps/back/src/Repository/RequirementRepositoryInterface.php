<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Requirement;

interface RequirementRepositoryInterface
{
    public function save(Requirement $requirement): void;

    public function delete(Requirement $requirement): void;

    public function findById(string $id): ?Requirement;

    /** @return Requirement[] */
    public function findAll(): array;
}
