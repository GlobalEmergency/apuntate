<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use Doctrine\ORM\EntityManagerInterface;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;

final class SignupForService
{
    public function __construct(
        private GapRepositoryInterface $gapRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function execute(User $user, string $serviceId, ?string $gapId = null): Gap
    {
        return $this->entityManager->wrapInTransaction(function () use ($user, $serviceId, $gapId): Gap {
            if (null !== $gapId) {
                return $this->signupForSpecificGap($user, $gapId);
            }

            return $this->signupForFirstAvailableGap($user, $serviceId);
        });
    }

    private function signupForSpecificGap(User $user, string $gapId): Gap
    {
        $gap = $this->gapRepository->findByIdForUpdate($gapId);

        if (null === $gap) {
            throw new \DomainException('Gap not found.');
        }

        $this->assertServiceIsBookable($user, $gap);

        if (null !== $gap->getUser()) {
            throw new \DomainException('This position is already taken.');
        }

        $this->validateRequirements($user, $gap);

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

        foreach ($availableGaps as $candidate) {
            if (!$this->userMeetsRequirements($user, $candidate)) {
                continue;
            }

            $locked = $this->gapRepository->findByIdForUpdate($candidate->getId()->toRfc4122());
            if (null === $locked || null !== $locked->getUser()) {
                continue;
            }

            $this->assertServiceIsBookable($user, $locked);

            $locked->setUser($user);
            $this->gapRepository->save($locked);

            return $locked;
        }

        throw new \DomainException('No available positions matching your qualifications.');
    }

    private function assertServiceIsBookable(User $user, Gap $gap): void
    {
        $service = $gap->getService();
        if (null === $service || ServiceStatus::CONFIRMED !== $service->getStatus()) {
            throw new \DomainException('This service is not accepting signups.');
        }

        if ($service->getDateStart()->isPast()) {
            throw new \DomainException('This service has already started.');
        }

        if ($this->gapRepository->hasOverlappingSignup($user, $service->getDateStart(), $service->getDateEnd())) {
            throw new \DomainException('You are already signed up for a service during this time.');
        }
    }

    /** @return string[] Names of missing requirements */
    private function findMissingRequirements(User $user, Gap $gap): array
    {
        $component = $gap->getUnitComponent()?->getComponent();
        if (null === $component) {
            return [];
        }

        $componentRequirements = $component->getRequirements();
        if ($componentRequirements->isEmpty()) {
            return [];
        }

        $userRequirementIds = $user->getRequirements()->map(
            fn ($r) => $r->getId()->toRfc4122()
        )->toArray();

        $missing = [];
        foreach ($componentRequirements as $req) {
            if (!\in_array($req->getId()->toRfc4122(), $userRequirementIds, true)) {
                $missing[] = $req->getName();
            }
        }

        return $missing;
    }

    private function validateRequirements(User $user, Gap $gap): void
    {
        $missing = $this->findMissingRequirements($user, $gap);

        if (!empty($missing)) {
            throw new \DomainException(sprintf('Missing required qualifications: %s.', implode(', ', $missing)));
        }
    }

    private function userMeetsRequirements(User $user, Gap $gap): bool
    {
        return empty($this->findMissingRequirements($user, $gap));
    }
}
