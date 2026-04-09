<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class RemoveUnitFromService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private UnitRepositoryInterface $unitRepository,
        private GapRepositoryInterface $gapRepository,
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

        if (!$service->getUnits()->contains($unit)) {
            throw new \DomainException('Unit is not associated with this service.');
        }

        $unitComponentIds = [];
        foreach ($unit->getUnitComponents() as $uc) {
            $unitComponentIds[] = $uc->getId()->toRfc4122();
        }

        foreach ($service->getGaps() as $gap) {
            $ucId = $gap->getUnitComponent()?->getId()?->toRfc4122();
            if (in_array($ucId, $unitComponentIds, true)) {
                if (null !== $gap->getUser()) {
                    throw new \DomainException('Cannot remove unit: some gaps have assigned users.');
                }
                $service->removeGap($gap);
                $this->gapRepository->delete($gap);
            }
        }

        $service->removeUnit($unit);
        $this->serviceRepository->save($service);

        return $service;
    }
}
