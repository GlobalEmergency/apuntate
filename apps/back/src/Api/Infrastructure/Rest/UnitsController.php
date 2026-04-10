<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\AssignRoleToUnit;
use GlobalEmergency\Apuntate\Application\Services\DecommissionUnit;
use GlobalEmergency\Apuntate\Application\Services\ListUnits;
use GlobalEmergency\Apuntate\Application\Services\RegisterUnit;
use GlobalEmergency\Apuntate\Application\Services\UnassignRoleFromUnit;
use GlobalEmergency\Apuntate\Application\Services\UpdateUnit;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;
use GlobalEmergency\Apuntate\Security\OrganizationVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/units', name: 'api_units_')]
final class UnitsController extends AbstractController
{
    public function __construct(
        private UnitRepositoryInterface $unitRepository,
        private OrganizationRepositoryInterface $organizationRepository,
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListUnits $listUnits): JsonResponse
    {
        return new JsonResponse(
            array_map(fn (Unit $u) => $this->serialize($u), $listUnits->execute()),
        );
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, RegisterUnit $registerUnit): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $organizationId = $data['organizationId'] ?? null;
        if (null === $organizationId) {
            return new JsonResponse(['error' => 'organizationId is required.'], Response::HTTP_BAD_REQUEST);
        }

        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organizationId);

        $organization = $this->organizationRepository->findById($organizationId);
        if (null === $organization) {
            return new JsonResponse(['error' => 'Organization not found.'], Response::HTTP_NOT_FOUND);
        }

        try {
            $unit = $registerUnit->execute(
                $organization,
                $data['name'] ?? '',
                $data['identifier'] ?? '',
                $data['speciality_id'] ?? null,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($unit), Response::HTTP_CREATED);
    }

    #[Route('/{unitId}', name: 'update', methods: ['PUT'])]
    public function update(string $unitId, Request $request, UpdateUnit $updateUnit): JsonResponse
    {
        $this->denyAccessUnlessGrantedForUnit($unitId);

        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $unit = $updateUnit->execute(
                $unitId,
                $data['name'] ?? null,
                $data['identifier'] ?? null,
                $data['speciality_id'] ?? null,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($unit));
    }

    #[Route('/{unitId}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $unitId, DecommissionUnit $decommissionUnit): JsonResponse
    {
        $this->denyAccessUnlessGrantedForUnit($unitId);

        try {
            $decommissionUnit->execute($unitId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{unitId}/roles', name: 'assign_role', methods: ['POST'])]
    public function assignRole(string $unitId, Request $request, AssignRoleToUnit $assignRole): JsonResponse
    {
        $this->denyAccessUnlessGrantedForUnit($unitId);

        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $uc = $assignRole->execute(
                $unitId,
                $data['component_id'] ?? '',
                $data['quantity'] ?? 1,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['id' => $uc->getId()->toRfc4122()], Response::HTTP_CREATED);
    }

    #[Route('/{unitId}/roles/{unitComponentId}', name: 'unassign_role', methods: ['DELETE'])]
    public function unassignRole(string $unitId, string $unitComponentId, UnassignRoleFromUnit $unassignRole): JsonResponse
    {
        $this->denyAccessUnlessGrantedForUnit($unitId);

        try {
            $unassignRole->execute($unitComponentId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function denyAccessUnlessGrantedForUnit(string $unitId): void
    {
        $unit = $this->unitRepository->findById($unitId);
        if (null === $unit) {
            throw $this->createNotFoundException('Unit not found.');
        }

        $organization = $unit->getOrganization();
        if (null === $organization) {
            throw $this->createAccessDeniedException('Unit has no organization.');
        }

        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization->getId()->toRfc4122());
    }

    /** @return array<string, mixed> */
    private function serialize(Unit $u): array
    {
        return [
            'id' => $u->getId()->toRfc4122(),
            'name' => $u->getName(),
            'identifier' => $u->getIdentifier(),
            'speciality' => $u->getSpeciality() ? [
                'id' => $u->getSpeciality()->getId()->toRfc4122(),
                'name' => $u->getSpeciality()->getName(),
            ] : null,
            'unitComponents' => array_map(fn ($uc) => [
                'id' => $uc->getId()->toRfc4122(),
                'quantity' => $uc->getQuantity(),
                'component' => $uc->getComponent() ? [
                    'id' => $uc->getComponent()->getId()->toRfc4122(),
                    'name' => $uc->getComponent()->getName(),
                ] : null,
            ], $u->getUnitComponents()->toArray()),
        ];
    }
}
