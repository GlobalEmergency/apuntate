<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;

final class GetUserProfile
{
    public function __construct(
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    /** @return array<string, mixed> */
    public function execute(User $user): array
    {
        $gaps = $this->gapRepository->findCompletedByUser($user);

        $totalServices = [];
        $totalHours = 0;

        foreach ($gaps as $gap) {
            $service = $gap->getService();
            if (null === $service) {
                continue;
            }

            $serviceId = $service->getId()->toRfc4122();
            if (!isset($totalServices[$serviceId])) {
                $totalServices[$serviceId] = true;

                $start = $service->getDateStart();
                $end = $service->getDateEnd();
                if ($start !== $end) {
                    $diff = $end->getTimestamp() - $start->getTimestamp();
                    $totalHours += max(0, $diff / 3600);
                }
            }
        }

        return [
            'id' => $user->getId()->toRfc4122(),
            'name' => $user->getName(),
            'surname' => $user->getSurname(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            'dateStart' => $user->getDateStart()?->format('Y-m-d'),
            'stats' => [
                'totalServices' => \count($totalServices),
                'totalHours' => round($totalHours, 1),
            ],
        ];
    }
}
