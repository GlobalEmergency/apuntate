<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Alert;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Repository\AlertRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;

final class NotifyNewService implements ServiceNotifierInterface
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private AlertRepositoryInterface $alertRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function execute(Service $service): int
    {
        $offset = 0;
        $totalAlerts = 0;

        do {
            $users = $this->userRepository->findBatch($offset, self::BATCH_SIZE);
            $alerts = [];

            foreach ($users as $user) {
                $alert = new Alert();
                $alert->setTitle('Nuevo servicio: '.$service->getName());
                $alert->setResume(sprintf(
                    'Se ha publicado el servicio "%s" para el %s.',
                    $service->getName(),
                    $service->getDateStart()->format('d/m/Y H:i'),
                ));
                $alert->setType('new_service');
                $alert->setRecipient($user);
                $alert->setService($service);
                $alerts[] = $alert;
            }

            if ([] !== $alerts) {
                $this->alertRepository->saveAll($alerts);
                $totalAlerts += \count($alerts);
            }

            $offset += self::BATCH_SIZE;
        } while (self::BATCH_SIZE === \count($users));

        return $totalAlerts;
    }
}
