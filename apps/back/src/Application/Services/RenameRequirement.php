<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Requirement;
use GlobalEmergency\Apuntate\Repository\RequirementRepositoryInterface;

final class RenameRequirement
{
    public function __construct(
        private RequirementRepositoryInterface $requirementRepository,
    ) {
    }

    public function execute(string $requirementId, string $name): Requirement
    {
        $requirement = $this->requirementRepository->findById($requirementId);
        if (null === $requirement) {
            throw new \DomainException('Requirement not found.');
        }

        if ('' === trim($name)) {
            throw new \DomainException('Requirement name cannot be empty.');
        }

        $requirement->setName($name);
        $this->requirementRepository->save($requirement);

        return $requirement;
    }
}
