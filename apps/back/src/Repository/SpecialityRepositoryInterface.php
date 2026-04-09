<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Speciality;

interface SpecialityRepositoryInterface
{
    public function findById(string $id): ?Speciality;

    /** @return Speciality[] */
    public function findAll(): array;

    public function save(Speciality $speciality): void;

    public function delete(Speciality $speciality): void;
}
