<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Repository\UnitRepository;

final class CreateGaps
{
    public function __construct(
        private UnitRepository $unitRepository,
    ) {
    }

    public function execute(Service $service, array $holes): Service
    {
        foreach ($holes as $unitId => $amount) {
            $unit = $this->unitRepository->find($unitId);
            if (null === $unit) {
                continue;
            }

            $nexted = [];
            $holesCreated = 0;

            foreach ($unit->getUnitComponents() as $unitComponent) {
                $gap = new Gap();
                $gap->setService($service);
                $gap->setUnitComponent($unitComponent);
                $service->addGap($gap);
                ++$holesCreated;

                if ($unitComponent->getQuantity() > 1) {
                    $nexted[] = [$unitComponent, $unitComponent->getQuantity() - 1];
                }
            }

            while ($amount > $holesCreated) {
                foreach ($nexted as $key => $value) {
                    $unitComponent = $value[0];
                    $rest = $value[1];

                    if ($holesCreated >= $amount) {
                        break;
                    }

                    $gap = new Gap();
                    $gap->setService($service);
                    $gap->setUnitComponent($unitComponent);
                    $service->addGap($gap);
                    ++$holesCreated;

                    if ($rest > 1) {
                        $nexted[$key][1] = $rest - 1;
                    } else {
                        unset($nexted[$key]);
                        break;
                    }
                }

                if (empty($nexted)) {
                    break;
                }
            }
        }

        return $service;
    }
}
