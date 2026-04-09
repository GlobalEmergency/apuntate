<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Speciality;
use GlobalEmergency\Apuntate\Repository\SpecialityRepositoryInterface;

final class UpdateSpeciality
{
    public function __construct(
        private SpecialityRepositoryInterface $specialityRepository,
    ) {
    }

    public function execute(string $specialityId, ?string $name = null, ?string $abbreviation = null): Speciality
    {
        $speciality = $this->specialityRepository->findById($specialityId);
        if (null === $speciality) {
            throw new \DomainException('Speciality not found.');
        }

        if (null !== $name) {
            if ('' === trim($name)) {
                throw new \DomainException('Speciality name cannot be empty.');
            }
            $speciality->setName($name);
        }

        if (null !== $abbreviation) {
            if ('' === trim($abbreviation)) {
                throw new \DomainException('Speciality abbreviation cannot be empty.');
            }
            $speciality->setAbbreviation($abbreviation);
        }

        $this->specialityRepository->save($speciality);

        return $speciality;
    }
}
