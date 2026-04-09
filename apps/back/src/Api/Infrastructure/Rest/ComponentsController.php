<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\CreateRole;
use GlobalEmergency\Apuntate\Application\Services\DeleteRole;
use GlobalEmergency\Apuntate\Application\Services\ListComponents;
use GlobalEmergency\Apuntate\Application\Services\UpdateRole;
use GlobalEmergency\Apuntate\Entity\Component;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/components', name: 'api_components_')]
final class ComponentsController extends AbstractController
{
    #[Route('', name: 'list', methods: ['GET'])]
    public function list(ListComponents $listComponents): JsonResponse
    {
        return new JsonResponse(
            array_map(fn (Component $c) => $this->serialize($c), $listComponents->execute()),
        );
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreateRole $createRole): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $component = $createRole->execute(
                $data['name'] ?? '',
                $data['requirement_ids'] ?? [],
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($component), Response::HTTP_CREATED);
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{componentId}', name: 'update', methods: ['PUT'])]
    public function update(string $componentId, Request $request, UpdateRole $updateRole): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $component = $updateRole->execute(
                $componentId,
                $data['name'] ?? null,
                $data['requirement_ids'] ?? null,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($component));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{componentId}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $componentId, DeleteRole $deleteRole): JsonResponse
    {
        try {
            $deleteRole->execute($componentId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /** @return array<string, mixed> */
    private function serialize(Component $c): array
    {
        return [
            'id' => $c->getId()->toRfc4122(),
            'name' => $c->getName(),
            'requirements' => array_map(fn ($r) => [
                'id' => $r->getId()->toRfc4122(),
                'name' => $r->getName(),
            ], $c->getRequirements()->toArray()),
        ];
    }
}
