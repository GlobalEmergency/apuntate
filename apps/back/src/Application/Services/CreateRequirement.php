<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Requirement;
use GlobalEmergency\Apuntate\Repository\RequirementRepositoryInterface;

final class CreateRequirement
{
    public function __construct(
        private RequirementRepositoryInterface $requirementRepository,
    ) {
    }

    public function execute(string $name): Requirement
    {
        if ('' === trim($name)) {
            throw new \DomainException('Name is required.');
        }

        $requirement = new Requirement();
        $requirement->setName($name);
        $this->requirementRepository->save($requirement);

        return $requirement;
    }
}
