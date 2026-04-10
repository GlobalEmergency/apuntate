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
        }

        if (null !== $status) {
            $parsed = ServiceStatus::tryFrom($status);
            if (null === $parsed) {
                throw new \DomainException(sprintf('Invalid service status: %s.', $status));
            }
            $this->assertValidTransition($service->getStatus(), $parsed);
        }

        if (null !== $dateStart || null !== $dateEnd || null !== $datePlace) {
            $effectiveStart = $dateStart ?? $service->getDateStart();
            $effectiveEnd = $dateEnd ?? $service->getDateEnd();
            $effectivePlace = $datePlace ?? $service->getDatePlace();
            if ($effectiveEnd <= $effectiveStart) {
                throw new \DomainException('End date must be after start date.');
            }
            if ($effectivePlace > $effectiveStart) {
                throw new \DomainException('Gathering time must be before or equal to start time.');
            }
        }

        if (null !== $name) {
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

        if (isset($parsed)) {
            $service->setStatus($parsed);
        }

        $this->serviceRepository->save($service);

        return $service;
    }

    /** @var array<string, ServiceStatus[]> */
    private const ALLOWED_TRANSITIONS = [
        'draft' => [ServiceStatus::CONFIRMED, ServiceStatus::CANCELLED],
        'requested' => [ServiceStatus::ACCEPTED, ServiceStatus::REJECTED, ServiceStatus::CANCELLED],
        'accepted' => [ServiceStatus::CONFIRMED, ServiceStatus::CANCELLED],
        'rejected' => [ServiceStatus::DRAFT],
        'confirmed' => [ServiceStatus::DEBRIEFING, ServiceStatus::CANCELLED],
        'debriefing' => [ServiceStatus::FINISHED],
        'cancelled' => [],
        'finished' => [],
    ];

    private function assertValidTransition(ServiceStatus $current, ServiceStatus $target): void
    {
        $allowed = self::ALLOWED_TRANSITIONS[$current->value];

        if (!\in_array($target, $allowed, true)) {
            throw new \DomainException(sprintf('Cannot transition from "%s" to "%s".', $current->value, $target->value));
        }
    }
}
