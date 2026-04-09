<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;

final class UpdateService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
    ) {
    }

    public function execute(
        string $serviceId,
        ?string $name = null,
        ?string $description = null,
        ?\DateTimeInterface $dateStart = null,
        ?\DateTimeInterface $dateEnd = null,
        ?\DateTimeInterface $datePlace = null,
        ?string $status = null,
    ): Service {
        $service = $this->serviceRepository->findById($serviceId);

        if (null === $service) {
            throw new \DomainException('Service not found.');
        }

        if (null !== $name) {
            if ('' === trim($name)) {
                throw new \DomainException('Service name cannot be empty.');
            }
            $service->setName($name);
        }

        if (null !== $description) {
            $service->setDescription($description);
        }

        if (null !== $dateStart) {
            $service->setDateStart($dateStart);
        }

        if (null !== $dateEnd) {
            $service->setDateEnd($dateEnd);
        }

        if (null !== $datePlace) {
            $service->setDatePlace($datePlace);
        }

        if (null !== $status) {
            $parsed = ServiceStatus::tryFrom($status);
            if (null === $parsed) {
                throw new \DomainException(sprintf('Invalid service status: %s.', $status));
            }
            $service->setStatus($parsed);
        }

        if ((null !== $dateStart || null !== $dateEnd) && $service->getDateEnd() <= $service->getDateStart()) {
            throw new \DomainException('End date must be after start date.');
        }

        $this->serviceRepository->save($service);

        return $service;
    }
}
