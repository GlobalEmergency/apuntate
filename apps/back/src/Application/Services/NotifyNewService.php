<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Alert;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Repository\AlertRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;

final class NotifyNewService implements ServiceNotifierInterface
{
    public function __construct(
        private AlertRepositoryInterface $alertRepository,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function execute(Service $service): int
    {
        $users = $this->userRepository->findAll();
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

        $this->alertRepository->saveAll($alerts);

        return \count($alerts);
    }
}
