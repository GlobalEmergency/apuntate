<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\ComponentRepositoryInterface;

final class ListComponents
{
    public function __construct(
        private ComponentRepositoryInterface $componentRepository,
    ) {
    }

    /** @return \GlobalEmergency\Apuntate\Entity\Component[] */
    public function execute(): array
    {
        return $this->componentRepository->findAll();
    }
}
