<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;

final class RemoveGap
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    public function execute(string $serviceId, string $gapId): void
    {
        $service = $this->serviceRepository->findById($serviceId);
        if (null === $service) {
            throw new \DomainException('Service not found.');
        }

        $gap = $this->gapRepository->findById($gapId);
        if (null === $gap) {
            throw new \DomainException('Gap not found.');
        }

        if ($gap->getService()?->getId()->toRfc4122() !== $service->getId()->toRfc4122()) {
            throw new \DomainException('Gap does not belong to this service.');
        }

        if (null !== $gap->getUser()) {
            throw new \DomainException('Cannot remove a gap with an assigned user.');
        }

        $service->removeGap($gap);
        $this->gapRepository->delete($gap);
    }
}
