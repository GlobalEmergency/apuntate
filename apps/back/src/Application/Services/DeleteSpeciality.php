<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\SpecialityRepositoryInterface;

final class DeleteSpeciality
{
    public function __construct(
        private SpecialityRepositoryInterface $specialityRepository,
    ) {
    }

    public function execute(string $specialityId): void
    {
        $speciality = $this->specialityRepository->findById($specialityId);
        if (null === $speciality) {
            throw new \DomainException('Speciality not found.');
        }

        if (!$speciality->getUnits()->isEmpty()) {
            throw new \DomainException('Cannot delete speciality: it has assigned units.');
        }

        $this->specialityRepository->delete($speciality);
    }
}
