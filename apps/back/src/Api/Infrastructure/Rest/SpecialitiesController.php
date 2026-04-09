<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\CreateSpeciality;
use GlobalEmergency\Apuntate\Application\Services\DeleteSpeciality;
use GlobalEmergency\Apuntate\Application\Services\ListSpecialities;
use GlobalEmergency\Apuntate\Application\Services\UpdateSpeciality;
use GlobalEmergency\Apuntate\Entity\Speciality;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/specialities', name: 'api_specialities_')]
final class SpecialitiesController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListSpecialities $listSpecialities): JsonResponse
    {
        return new JsonResponse(array_map($this->serialize(...), $listSpecialities->execute()));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreateSpeciality $createSpeciality): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $speciality = $createSpeciality->execute(
                $data['name'] ?? '',
                $data['abbreviation'] ?? '',
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($speciality), Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{specialityId}', name: 'update', methods: ['PUT'])]
    public function update(string $specialityId, Request $request, UpdateSpeciality $updateSpeciality): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $speciality = $updateSpeciality->execute(
                $specialityId,
                $data['name'] ?? null,
                $data['abbreviation'] ?? null,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($speciality));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{specialityId}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $specialityId, DeleteSpeciality $deleteSpeciality): JsonResponse
    {
        try {
            $deleteSpeciality->execute($specialityId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /** @return array<string, mixed> */
    private function serialize(Speciality $s): array
    {
        return [
            'id' => $s->getId()->toRfc4122(),
            'name' => $s->getName(),
            'abbreviation' => $s->getAbbreviation(),
        ];
    }
}
