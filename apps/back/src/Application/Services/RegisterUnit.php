<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Repository\SpecialityRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class RegisterUnit
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
        private SpecialityRepositoryInterface $specialityRepository,
    ) {
    }

    public function execute(Organization $organization, string $name, string $identifier, ?string $specialityId = null): Unit
    {
        if ('' === trim($name)) {
            throw new \DomainException('Unit name is required.');
        }

        if ('' === trim($identifier)) {
            throw new \DomainException('Unit identifier is required.');
        }

        $unit = new Unit();
        $unit->setOrganization($organization);
        $unit->setName($name);
        $unit->setIdentifier($identifier);

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
