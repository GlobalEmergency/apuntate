<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;

final class CancelService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
    ) {
    }

    public function execute(string $serviceId): void
    {
        $service = $this->serviceRepository->findById($serviceId);

        if (null === $service) {
            throw new \DomainException('Service not found.');
        }

        $currentStatus = $service->getStatus();
        if (ServiceStatus::CANCELLED === $currentStatus || ServiceStatus::FINISHED === $currentStatus) {
            throw new \DomainException('Cannot cancel a service that is already '.$currentStatus->value.'.');
        }

        $service->setStatus(ServiceStatus::CANCELLED);
        $this->serviceRepository->save($service);
    }
}
