<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Repository\ComponentRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\RequirementRepositoryInterface;

final class UpdateRole
{
    public function __construct(
        private ComponentRepositoryInterface $componentRepository,
        private RequirementRepositoryInterface $requirementRepository,
    ) {
    }

    /** @param string[]|null $requirementIds */
    public function execute(string $componentId, ?string $name = null, ?array $requirementIds = null): Component
    {
        $component = $this->componentRepository->findById($componentId);
        if (null === $component) {
            throw new \DomainException('Role not found.');
        }

        if (null !== $name) {
            if ('' === trim($name)) {
                throw new \DomainException('Role name cannot be empty.');
            }
            $component->setName($name);
        }

        if (null !== $requirementIds) {
            foreach ($component->getRequirements()->toArray() as $req) {
                $component->removeRequirement($req);
            }
            foreach ($requirementIds as $reqId) {
                $requirement = $this->requirementRepository->findById($reqId);
                if (null === $requirement) {
                    throw new \DomainException(sprintf('Requirement %s not found.', $reqId));
                }
                $component->addRequirement($requirement);
            }
        }

        $this->componentRepository->save($component);

        return $component;
    }
}
