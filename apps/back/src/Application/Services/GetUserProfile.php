<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use Carbon\Carbon;
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

                $start = Carbon::instance($service->getDateStart());
                $end = Carbon::instance($service->getDateEnd());
                $totalHours += max(0, $start->diffInHours($end));
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
            'organizations' => array_map(fn ($m) => [
                'id' => $m->getOrganization()->getId()->toRfc4122(),
                'name' => $m->getOrganization()->getName(),
                'role' => $m->getRole(),
            ], $user->getMemberships()->toArray()),
            'requirements' => array_map(fn ($r) => [
                'id' => $r->getId()->toRfc4122(),
                'name' => $r->getName(),
            ], $user->getRequirements()->toArray()),
        ];
    }
}
