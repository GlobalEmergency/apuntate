<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class CreateGaps
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
    ) {
    }

    /** @param array<string, int> $holes */
    public function execute(Service $service, array $holes): Service
    {
        foreach ($holes as $unitId => $amount) {
            $unit = $this->unitRepository->findById($unitId);
            if (null === $unit) {
                throw new \DomainException(sprintf('Unit not found: %s.', $unitId));
            }

            $this->generateGapsForUnit($service, $unit, $amount);
        }

        return $service;
    }

    private function generateGapsForUnit(Service $service, Unit $unit, int $amount): void
    {
        $gapsCreated = 0;

        // First pass: create one gap per unit component
        $expandableComponents = [];
        foreach ($unit->getUnitComponents() as $unitComponent) {
            $service->addGap($this->createGap($service, $unitComponent));
            ++$gapsCreated;

            if ($unitComponent->getQuantity() > 1) {
                $expandableComponents[] = [$unitComponent, $unitComponent->getQuantity() - 1];
            }
        }

        // Expansion pass: fill remaining slots by repeating components up to their quantity
        while ($gapsCreated < $amount && !empty($expandableComponents)) {
            foreach ($expandableComponents as $key => [$unitComponent, $remainingSlots]) {
                if ($gapsCreated >= $amount) {
                    break;
                }

                $service->addGap($this->createGap($service, $unitComponent));
                ++$gapsCreated;

                if ($remainingSlots > 1) {
                    $expandableComponents[$key][1] = $remainingSlots - 1;
                } else {
                    unset($expandableComponents[$key]);
                    break;
                }
            }
        }
    }

    private function createGap(Service $service, \GlobalEmergency\Apuntate\Entity\UnitComponent $unitComponent): Gap
    {
        $gap = new Gap();
        $gap->setService($service);
        $gap->setUnitComponent($unitComponent);

        return $gap;
    }
}
