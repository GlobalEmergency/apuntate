<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Speciality;
use GlobalEmergency\Apuntate\Repository\SpecialityRepositoryInterface;

final class CreateSpeciality
{
    public function __construct(
        private SpecialityRepositoryInterface $specialityRepository,
    ) {
    }

    public function execute(string $name, string $abbreviation): Speciality
    {
        if ('' === trim($name)) {
            throw new \DomainException('Speciality name is required.');
        }

        if ('' === trim($abbreviation)) {
            throw new \DomainException('Speciality abbreviation is required.');
        }

        $speciality = new Speciality();
        $speciality->setName($name);
        $speciality->setAbbreviation($abbreviation);

        $this->specialityRepository->save($speciality);

        return $speciality;
    }
}
