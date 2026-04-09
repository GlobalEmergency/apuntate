<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Repository\ComponentRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\RequirementRepositoryInterface;

final class CreateRole
{
    public function __construct(
        private ComponentRepositoryInterface $componentRepository,
        private RequirementRepositoryInterface $requirementRepository,
    ) {
    }

    /** @param string[] $requirementIds */
    public function execute(string $name, array $requirementIds = []): Component
    {
        if ('' === trim($name)) {
            throw new \DomainException('Role name is required.');
        }

        $component = new Component();
        $component->setName($name);

        foreach ($requirementIds as $reqId) {
            $requirement = $this->requirementRepository->findById($reqId);
            if (null === $requirement) {
                throw new \DomainException(sprintf('Requirement %s not found.', $reqId));
            }
            $component->addRequirement($requirement);
        }

        $this->componentRepository->save($component);

        return $component;
    }
}
