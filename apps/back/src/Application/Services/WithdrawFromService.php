<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use Doctrine\ORM\EntityManagerInterface;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;

final class WithdrawFromService
{
    public function __construct(
        private GapRepositoryInterface $gapRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(User $user, string $gapId): void
    {
        $this->entityManager->wrapInTransaction(function () use ($user, $gapId): void {
            $gap = $this->gapRepository->findByIdForUpdate($gapId);

            if (null === $gap) {
                throw new \DomainException('Gap not found.');
            }

            if (null === $gap->getUser() || $gap->getUser()->getId()->toRfc4122() !== $user->getId()->toRfc4122()) {
                throw new \DomainException('You are not signed up for this position.');
            }

            $gap->setUser(null);
            $this->gapRepository->save($gap);
        });
    }
}
