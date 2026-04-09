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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/units', name: 'api_units_')]
final class UnitsController extends AbstractController
{
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

        try {
            $unit = $registerUnit->execute(
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
        try {
            $unassignRole->execute($unitComponentId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

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
