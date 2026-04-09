<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Organization;

interface OrganizationRepositoryInterface
{
    public function save(Organization $organization): void;

    public function findById(string $id): ?Organization;

    public function findBySlug(string $slug): ?Organization;

    public function removeMember(\GlobalEmergency\Apuntate\Entity\OrganizationMember $member): void;
}
