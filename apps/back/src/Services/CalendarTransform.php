<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Services;

use GlobalEmergency\Apuntate\Entity\Service;

final class CalendarTransform
{
    /**
     * @param Service[] $services
     *
     * @return array<int, array<string, mixed>>
     */
    public static function transformServices(array $services): array
    {
        $events = [];
        foreach ($services as $service) {
            $events[] = self::transformService($service);
        }

        return $events;
    }

    /** @return array<string, mixed> */
    public static function transformService(Service $service): array
    {
        return [
            'id' => (string) $service->getId(),
            'title' => $service->getName(),
            'start' => $service->getDateStart()->utc()->format('Y-m-d\TH:i:s\Z'),
            'end' => $service->getDateEnd()->utc()->format('Y-m-d\TH:i:s\Z'),
            'allDay' => false,
        ];
    }
}
