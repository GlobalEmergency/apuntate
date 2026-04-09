<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;

final class CreateGaps
{
    public function executeForUnit(Service $service, Unit $unit): void
    {
        foreach ($unit->getUnitComponents() as $unitComponent) {
            for ($i = 0; $i < $unitComponent->getQuantity(); ++$i) {
                $service->addGap($this->createGap($service, $unitComponent));
            }
        }
    }

    private function createGap(Service $service, UnitComponent $unitComponent): Gap
    {
        $gap = new Gap();
        $gap->setService($service);
        $gap->setUnitComponent($unitComponent);

        return $gap;
    }
}
