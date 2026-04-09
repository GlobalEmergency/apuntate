<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Component;

interface ComponentRepositoryInterface
{
    public function findById(string $id): ?Component;

    /** @return Component[] */
    public function findAll(): array;

    public function save(Component $component): void;

    public function delete(Component $component): void;
}
