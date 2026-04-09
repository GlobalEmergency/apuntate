<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;

final class ListUnits
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
    ) {
    }

    /** @return \GlobalEmergency\Apuntate\Entity\Unit[] */
    public function execute(): array
    {
        return $this->unitRepository->findAll();
    }
}
