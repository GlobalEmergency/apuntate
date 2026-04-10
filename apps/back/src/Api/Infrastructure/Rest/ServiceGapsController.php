<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\CreateGap;
use GlobalEmergency\Apuntate\Application\Services\RemoveGap;
use GlobalEmergency\Apuntate\Security\OrganizationAccessChecker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/services/{serviceId}/gaps', name: 'api_service_gaps_')]
final class ServiceGapsController extends AbstractController
{
    public function __construct(
        private OrganizationAccessChecker $accessChecker,
    ) {
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(
        string $serviceId,
        Request $request,
        CreateGap $createGap,
    ): JsonResponse {
        $this->accessChecker->denyUnlessCanManageService($serviceId);
        $data = json_decode($request->getContent(), true) ?? [];
        $unitComponentId = $data['unit_component_id'] ?? null;

        if (null === $unitComponentId) {
            return new JsonResponse(
                ['error' => 'unit_component_id is required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $gap = $createGap->execute($serviceId, $unitComponentId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            ['id' => $gap->getId()->toRfc4122()],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{gapId}', name: 'remove', methods: ['DELETE'])]
    public function remove(
        string $serviceId,
        string $gapId,
        RemoveGap $removeGap,
    ): JsonResponse {
        $this->accessChecker->denyUnlessCanManageService($serviceId);

        try {
            $removeGap->execute($serviceId, $gapId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
