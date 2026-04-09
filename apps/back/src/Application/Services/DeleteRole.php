<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\ComponentRepositoryInterface;

final class DeleteRole
{
    public function __construct(
        private ComponentRepositoryInterface $componentRepository,
    ) {
    }

    public function execute(string $componentId): void
    {
        $component = $this->componentRepository->findById($componentId);
        if (null === $component) {
            throw new \DomainException('Role not found.');
        }

        if (!$component->getUnitComponents()->isEmpty()) {
            throw new \DomainException('Cannot delete role: it is assigned to units.');
        }

        $this->componentRepository->delete($component);
    }
}
