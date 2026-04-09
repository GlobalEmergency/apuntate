<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;

final class PublishService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private ServiceNotifierInterface $notifyNewService,
    ) {
    }

    public function execute(string $serviceId): Service
    {
        $service = $this->serviceRepository->findById($serviceId);

        if (null === $service) {
            throw new \DomainException('Service not found.');
        }

        $status = $service->getStatus();
        if (ServiceStatus::DRAFT !== $status) {
            throw new \DomainException(sprintf('Only draft services can be published. Current status: %s.', $status->value));
        }

        $service->setStatus(ServiceStatus::CONFIRMED);
        $this->serviceRepository->save($service);

        $this->notifyNewService->execute($service);

        return $service;
    }
}
