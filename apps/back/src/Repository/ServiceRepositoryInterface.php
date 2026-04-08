<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\Service;

interface ServiceRepositoryInterface
{
    public function save(Service $service): void;

    public function delete(Service $service): void;

    public function findById(string $id): ?Service;

    public function findUpcoming(): array;

    public function findBetweenDates(\DateTimeInterface $start, \DateTimeInterface $end): array;
}
