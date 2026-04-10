<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\AddUnitToService;
use GlobalEmergency\Apuntate\Application\Services\RemoveUnitFromService;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Security\OrganizationVoter;
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
        private ServiceRepositoryInterface $serviceRepository,
    ) {
    }

    #[Route('/{unitId}', name: 'add', methods: ['POST'])]
    public function add(
        string $serviceId,
        string $unitId,
        AddUnitToService $addUnitToService,
    ): JsonResponse {
        $this->denyAccessUnlessGrantedForService($serviceId);

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
        $this->denyAccessUnlessGrantedForService($serviceId);

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

    private function denyAccessUnlessGrantedForService(string $serviceId): void
    {
        $service = $this->serviceRepository->findById($serviceId);
        if (null === $service) {
            throw $this->createNotFoundException('Service not found.');
        }

        $organization = $service->getOrganization();
        if (null === $organization) {
            throw $this->createAccessDeniedException('Service has no organization.');
        }

        $this->denyAccessUnlessGranted(OrganizationVoter::MANAGE, $organization->getId()->toRfc4122());
    }
}
