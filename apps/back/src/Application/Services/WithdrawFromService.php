<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;

final class WithdrawFromService
{
    public function __construct(
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    public function execute(User $user, string $gapId): void
    {
        $gap = $this->gapRepository->findById($gapId);

        if (null === $gap) {
            throw new \DomainException('Gap not found.');
        }

        if (null === $gap->getUser() || $gap->getUser()->getId()->toRfc4122() !== $user->getId()->toRfc4122()) {
            throw new \DomainException('You are not signed up for this position.');
        }

        $gap->setUser(null);
        $this->gapRepository->save($gap);
    }
}
