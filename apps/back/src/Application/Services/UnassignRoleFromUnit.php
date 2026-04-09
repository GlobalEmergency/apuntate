<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\UnitComponentRepositoryInterface;

final class UnassignRoleFromUnit
{
    public function __construct(
        private UnitComponentRepositoryInterface $unitComponentRepository,
    ) {
    }

    public function execute(string $unitComponentId): void
    {
        $unitComponent = $this->unitComponentRepository->findById($unitComponentId);
        if (null === $unitComponent) {
            throw new \DomainException('Unit component not found.');
        }

        $this->unitComponentRepository->delete($unitComponent);
    }
}
