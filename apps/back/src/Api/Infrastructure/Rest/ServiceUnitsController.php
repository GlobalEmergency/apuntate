<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\AddUnitToService;
use GlobalEmergency\Apuntate\Application\Services\RemoveUnitFromService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/services/{serviceId}/units', name: 'api_service_units_')]
final class ServiceUnitsController extends AbstractController
{
    public function __construct(
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/{unitId}', name: 'add', methods: ['POST'])]
    public function add(
        string $serviceId,
        string $unitId,
        AddUnitToService $addUnitToService,
    ): JsonResponse {
        try {
            $service = $addUnitToService->execute($serviceId, $unitId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            $this->serializer->serialize($service, 'json'),
            Response::HTTP_CREATED,
            [],
            true,
        );
    }

    #[Route('/{unitId}', name: 'remove', methods: ['DELETE'])]
    public function remove(
        string $serviceId,
        string $unitId,
        RemoveUnitFromService $removeUnitFromService,
    ): JsonResponse {
        try {
            $service = $removeUnitFromService->execute($serviceId, $unitId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            $this->serializer->serialize($service, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }
}
