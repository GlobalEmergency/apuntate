<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Repository\SpecialityRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class UpdateUnit
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
        private SpecialityRepositoryInterface $specialityRepository,
    ) {
    }

    public function execute(
        string $unitId,
        ?string $name = null,
        ?string $identifier = null,
        ?string $specialityId = null,
    ): Unit {
        $unit = $this->unitRepository->findById($unitId);
        if (null === $unit) {
            throw new \DomainException('Unit not found.');
        }

        if (null !== $name) {
            if ('' === trim($name)) {
                throw new \DomainException('Unit name cannot be empty.');
            }
            $unit->setName($name);
        }

        if (null !== $identifier) {
            if ('' === trim($identifier)) {
                throw new \DomainException('Unit identifier cannot be empty.');
            }
            $unit->setIdentifier($identifier);
        }

        if (null !== $specialityId) {
            $speciality = $this->specialityRepository->findById($specialityId);
            if (null === $speciality) {
                throw new \DomainException('Speciality not found.');
            }
            $unit->setSpeciality($speciality);
        }

        $this->unitRepository->save($unit);

        return $unit;
    }
}
