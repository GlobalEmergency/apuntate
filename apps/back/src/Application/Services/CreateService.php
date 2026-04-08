<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;

final class CreateService
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
    ) {
    }

    public function execute(
        string $name,
        \DateTimeInterface $dateStart,
        \DateTimeInterface $dateEnd,
        \DateTimeInterface $datePlace,
        ?string $description = null,
    ): Service {
        if ('' === trim($name)) {
            throw new \DomainException('Service name is required.');
        }

        if ($dateEnd <= $dateStart) {
            throw new \DomainException('End date must be after start date.');
        }

        $service = new Service();
        $service->setName($name);
        $service->setDescription($description);
        $service->setDateStart($dateStart);
        $service->setDateEnd($dateEnd);
        $service->setDatePlace($datePlace);
        $service->setStatus(ServiceStatus::DRAFT);

        $this->serviceRepository->save($service);

        return $service;
    }
}
