<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use Carbon\Carbon;
use GlobalEmergency\Apuntate\Application\Services\CancelService;
use GlobalEmergency\Apuntate\Application\Services\CreateService;
use GlobalEmergency\Apuntate\Application\Services\PublishService;
use GlobalEmergency\Apuntate\Application\Services\UpdateService;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Services\CalendarTransform;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/services', name: 'api_services_')]
final class ServicesController extends AbstractController
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
        private SerializerInterface $serializer,
    ) {
    }

    #[Route('/nexts', name: 'nexts', methods: ['GET'])]
    public function nexts(): JsonResponse
    {
        $services = $this->serviceRepository->findUpcoming();

        return new JsonResponse(
            $this->serializer->serialize($services, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(Request $request): JsonResponse
    {
        $dateStart = Carbon::parseFromLocale(
            $request->get('s', Carbon::now()->startOfMonth()->format('d-m-Y'))
        )->startOfDay();
        $dateEnd = Carbon::parseFromLocale(
            $request->get('e', Carbon::now()->endOfMonth()->format('d-m-Y'))
        )->endOfDay();

        $services = $this->serviceRepository->findBetweenDates($dateStart, $dateEnd);

        return new JsonResponse(CalendarTransform::transformServices($services));
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreateService $createService): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $service = $createService->execute(
                name: $data['name'] ?? '',
                dateStart: new \DateTimeImmutable($data['dateStart'] ?? 'now'),
                dateEnd: new \DateTimeImmutable($data['dateEnd'] ?? 'now'),
                datePlace: new \DateTimeImmutable($data['datePlace'] ?? 'now'),
                description: $data['description'] ?? null,
            );
        } catch (\DomainException|\DateMalformedStringException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            ['id' => $service->getId()->toRfc4122(), 'name' => $service->getName()],
            Response::HTTP_CREATED,
        );
    }

    #[Route('/{serviceId}', name: 'get', methods: ['GET'])]
    public function get(string $serviceId): JsonResponse
    {
        $service = $this->serviceRepository->findById($serviceId);

        if (null === $service) {
            return new JsonResponse(['error' => 'Service not found.'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(
            $this->serializer->serialize($service, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/{serviceId}', name: 'update', methods: ['PUT'])]
    public function update(string $serviceId, Request $request, UpdateService $updateService): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $service = $updateService->execute(
                serviceId: $serviceId,
                name: $data['name'] ?? null,
                description: $data['description'] ?? null,
                dateStart: isset($data['dateStart']) ? new \DateTimeImmutable($data['dateStart']) : null,
                dateEnd: isset($data['dateEnd']) ? new \DateTimeImmutable($data['dateEnd']) : null,
                datePlace: isset($data['datePlace']) ? new \DateTimeImmutable($data['datePlace']) : null,
                status: $data['status'] ?? null,
            );
        } catch (\DomainException|\DateMalformedStringException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(
            $this->serializer->serialize($service, 'json'),
            Response::HTTP_OK,
            [],
            true,
        );
    }

    #[Route('/{serviceId}/publish', name: 'publish', methods: ['POST'])]
    public function publish(string $serviceId, PublishService $publishService): JsonResponse
    {
        try {
            $service = $publishService->execute($serviceId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'id' => $service->getId()->toRfc4122(),
            'status' => $service->getStatus()->value,
        ]);
    }

    #[Route('/{serviceId}', name: 'cancel', methods: ['DELETE'])]
    public function cancel(string $serviceId, CancelService $cancelService): JsonResponse
    {
        try {
            $cancelService->execute($serviceId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
