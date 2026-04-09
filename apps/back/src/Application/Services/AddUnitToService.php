<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class AddUnitToService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private UnitRepositoryInterface $unitRepository,
        private CreateGaps $createGaps,
    ) {
    }

    public function execute(string $serviceId, string $unitId): Service
    {
        $service = $this->serviceRepository->findById($serviceId);
        if (null === $service) {
            throw new \DomainException('Service not found.');
        }

        $unit = $this->unitRepository->findById($unitId);
        if (null === $unit) {
            throw new \DomainException('Unit not found.');
        }

        if ($service->getUnits()->contains($unit)) {
            throw new \DomainException('Unit is already associated with this service.');
        }

        $service->addUnit($unit);
        $this->createGaps->execute($service, [$unitId => $unit->componentsMax()]);
        $this->serviceRepository->save($service);

        return $service;
    }
}
