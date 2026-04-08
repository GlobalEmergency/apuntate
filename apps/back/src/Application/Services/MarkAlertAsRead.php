<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\AlertRepositoryInterface;

final class MarkAlertAsRead
{
    public function __construct(
        private AlertRepositoryInterface $alertRepository,
    ) {
    }

    public function execute(User $user, string $alertId): void
    {
        $alert = $this->alertRepository->findById($alertId);

        if (null === $alert) {
            throw new \DomainException('Alert not found.');
        }

        if ($alert->getRecipient()->getId()->toRfc4122() !== $user->getId()->toRfc4122()) {
            throw new \DomainException('This alert does not belong to you.');
        }

        $alert->markAsRead();
        $this->alertRepository->save($alert);
    }
}
