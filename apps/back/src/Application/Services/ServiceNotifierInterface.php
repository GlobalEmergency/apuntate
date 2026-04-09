<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Service;

interface ServiceNotifierInterface
{
    public function execute(Service $service): int;
}
