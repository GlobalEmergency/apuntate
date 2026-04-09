<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\SpecialityRepositoryInterface;

final class ListSpecialities
{
    public function __construct(
        private SpecialityRepositoryInterface $specialityRepository,
    ) {
    }

    /** @return \GlobalEmergency\Apuntate\Entity\Speciality[] */
    public function execute(): array
    {
        return $this->specialityRepository->findAll();
    }
}
