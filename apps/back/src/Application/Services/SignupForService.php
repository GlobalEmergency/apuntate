<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;

final class SignupForService
{
    public function __construct(
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    public function execute(User $user, string $serviceId, ?string $gapId = null): Gap
    {
        if (null !== $gapId) {
            return $this->signupForSpecificGap($user, $gapId);
        }

        return $this->signupForFirstAvailableGap($user, $serviceId);
    }

    private function signupForSpecificGap(User $user, string $gapId): Gap
    {
        $gap = $this->gapRepository->findById($gapId);

        if (null === $gap) {
            throw new \DomainException('Gap not found.');
        }

        if (null !== $gap->getUser()) {
            throw new \DomainException('This position is already taken.');
        }

        $gap->setUser($user);
        $this->gapRepository->save($gap);

        return $gap;
    }

    private function signupForFirstAvailableGap(User $user, string $serviceId): Gap
    {
        $availableGaps = $this->gapRepository->findAvailableByService($serviceId);

        if (0 === \count($availableGaps)) {
            throw new \DomainException('No available positions for this service.');
        }

        $gap = $availableGaps[0];
        $gap->setUser($user);
        $this->gapRepository->save($gap);

        return $gap;
    }
}
