<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitComponentRepositoryInterface;

final class CreateGap
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private UnitComponentRepositoryInterface $unitComponentRepository,
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    public function execute(string $serviceId, string $unitComponentId): Gap
    {
        $service = $this->serviceRepository->findById($serviceId);
        if (null === $service) {
            throw new \DomainException('Service not found.');
        }

        $unitComponent = $this->unitComponentRepository->findById($unitComponentId);
        if (null === $unitComponent) {
            throw new \DomainException('Unit component not found.');
        }

        $gap = new Gap();
        $gap->setService($service);
        $gap->setUnitComponent($unitComponent);
        $service->addGap($gap);

        $this->gapRepository->save($gap);

        return $gap;
    }
}
