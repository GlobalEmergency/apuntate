<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Security;

use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class OrganizationAccessChecker
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private UnitRepositoryInterface $unitRepository,
        private Security $security,
    ) {
    }

    public function denyUnlessCanManageService(string $serviceId): void
    {
        $service = $this->serviceRepository->findById($serviceId);
        if (null === $service) {
            throw new NotFoundHttpException('Service not found.');
        }

        $organization = $service->getOrganization();
        if (null === $organization) {
            throw new AccessDeniedHttpException('Service has no organization.');
        }

        if (!$this->security->isGranted(OrganizationVoter::MANAGE, $organization->getId()->toRfc4122())) {
            throw new AccessDeniedHttpException('You do not have permission to manage this service.');
        }
    }

    public function denyUnlessCanManageUnit(string $unitId): void
    {
        $unit = $this->unitRepository->findById($unitId);
        if (null === $unit) {
            throw new NotFoundHttpException('Unit not found.');
        }

        $organization = $unit->getOrganization();
        if (null === $organization) {
            throw new AccessDeniedHttpException('Unit has no organization.');
        }

        if (!$this->security->isGranted(OrganizationVoter::MANAGE, $organization->getId()->toRfc4122())) {
            throw new AccessDeniedHttpException('You do not have permission to manage this unit.');
        }
    }
}
