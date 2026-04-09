<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class DecommissionUnit
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    public function execute(string $unitId): void
    {
        $unit = $this->unitRepository->findById($unitId);
        if (null === $unit) {
            throw new \DomainException('Unit not found.');
        }

        foreach ($unit->getServices() as $service) {
            $status = $service->getStatus();
            if (in_array($status, [ServiceStatus::DRAFT, ServiceStatus::CONFIRMED], true)) {
                throw new \DomainException('Cannot decommission unit: it is assigned to active services.');
            }
        }

        $this->unitRepository->delete($unit);
    }
}
