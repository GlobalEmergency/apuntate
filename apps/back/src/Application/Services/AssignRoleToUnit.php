<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\UnitComponent;
use GlobalEmergency\Apuntate\Repository\ComponentRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitComponentRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class AssignRoleToUnit
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
        private ComponentRepositoryInterface $componentRepository,
        private UnitComponentRepositoryInterface $unitComponentRepository,
    ) {
    }

    public function execute(string $unitId, string $componentId, int $quantity = 1): UnitComponent
    {
        $unit = $this->unitRepository->findById($unitId);
        if (null === $unit) {
            throw new \DomainException('Unit not found.');
        }

        $component = $this->componentRepository->findById($componentId);
        if (null === $component) {
            throw new \DomainException('Component not found.');
        }

        if ($quantity < 1) {
            throw new \DomainException('Quantity must be at least 1.');
        }

        $unitComponent = new UnitComponent();
        $unitComponent->setUnit($unit);
        $unitComponent->setComponent($component);
        $unitComponent->setQuantity($quantity);

        $this->unitComponentRepository->save($unitComponent);

        return $unitComponent;
    }
}
