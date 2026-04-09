<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use Carbon\Carbon;
use GlobalEmergency\Apuntate\Application\Services\CancelService;
use GlobalEmergency\Apuntate\Application\Services\CreateService;
use GlobalEmergency\Apuntate\Application\Services\PublishService;
use GlobalEmergency\Apuntate\Application\Services\UpdateService;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Services\CalendarTransform;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/services', name: 'api_services_')]
final class ServicesController extends AbstractController
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
    ) {
    }

    #[Route('/nexts', name: 'nexts', methods: ['GET'])]
    public function nexts(): JsonResponse
    {
        $services = $this->serviceRepository->findUpcoming();

        return new JsonResponse(
            array_map(fn (Service $s) => $this->serialize($s), $services),
        );
    }

    #[Route('/calendar', name: 'calendar', methods: ['GET'])]
    public function calendar(Request $request): JsonResponse
    {
        $dateStart = Carbon::parse(
            $request->get('s', Carbon::now('UTC')->startOfMonth()->toIso8601String())
        )->utc()->startOfDay();
        $dateEnd = Carbon::parse(
            $request->get('e', Carbon::now('UTC')->endOfMonth()->toIso8601String())
        )->utc()->endOfDay();

        $services = $this->serviceRepository->findBetweenDates($dateStart, $dateEnd);

        return new JsonResponse(CalendarTransform::transformServices($services));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request, CreateService $createService): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $service = $createService->execute(
                name: $data['name'] ?? '',
                dateStart: new \DateTimeImmutable($data['dateStart'] ?? 'now', new \DateTimeZone('UTC')),
                dateEnd: new \DateTimeImmutable($data['dateEnd'] ?? 'now', new \DateTimeZone('UTC')),
                datePlace: new \DateTimeImmutable($data['datePlace'] ?? 'now', new \DateTimeZone('UTC')),
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

        return new JsonResponse($this->serialize($service));
    }

    #[IsGranted('ROLE_ADMIN')]
    #[Route('/{serviceId}', name: 'update', methods: ['PUT'])]
    public function update(string $serviceId, Request $request, UpdateService $updateService): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $service = $updateService->execute(
                serviceId: $serviceId,
                name: $data['name'] ?? null,
                description: $data['description'] ?? null,
                dateStart: isset($data['dateStart']) ? new \DateTimeImmutable($data['dateStart'], new \DateTimeZone('UTC')) : null,
                dateEnd: isset($data['dateEnd']) ? new \DateTimeImmutable($data['dateEnd'], new \DateTimeZone('UTC')) : null,
                datePlace: isset($data['datePlace']) ? new \DateTimeImmutable($data['datePlace'], new \DateTimeZone('UTC')) : null,
                status: $data['status'] ?? null,
            );
        } catch (\DomainException|\DateMalformedStringException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($this->serialize($service));
    }

    #[IsGranted('ROLE_ADMIN')]
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

    #[IsGranted('ROLE_ADMIN')]
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

    /** @return array<string, mixed> */
    private function serialize(Service $s): array
    {
        return [
            'id' => $s->getId()->toRfc4122(),
            'name' => $s->getName(),
            'description' => $s->getDescription(),
            'dateStart' => (clone $s->getDateStart())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
            'dateEnd' => (clone $s->getDateEnd())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
            'datePlace' => (clone $s->getDatePlace())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
            'status' => $s->getStatus()->value,
            'units' => array_map(fn ($u) => [
                'id' => $u->getId()->toRfc4122(),
                'name' => $u->getName(),
                'identifier' => $u->getIdentifier(),
            ], $s->getUnits()->toArray()),
            'gaps' => array_map(fn ($g) => [
                'id' => $g->getId()->toRfc4122(),
                'user' => $g->getUser() ? [
                    'id' => (string) $g->getUser()->getId(),
                    'name' => $g->getUser()->getName(),
                    'email' => $g->getUser()->getEmail(),
                ] : null,
                'unitComponent' => $g->getUnitComponent() ? [
                    'id' => $g->getUnitComponent()->getId()->toRfc4122(),
                    'quantity' => $g->getUnitComponent()->getQuantity(),
                    'component' => $g->getUnitComponent()->getComponent() ? [
                        'id' => $g->getUnitComponent()->getComponent()->getId()->toRfc4122(),
                        'name' => $g->getUnitComponent()->getComponent()->getName(),
                    ] : null,
                    'unit' => $g->getUnitComponent()->getUnit() ? [
                        'id' => $g->getUnitComponent()->getUnit()->getId()->toRfc4122(),
                        'name' => $g->getUnitComponent()->getUnit()->getName(),
                    ] : null,
                ] : null,
            ], $s->getGaps()->toArray()),
            'createdAt' => (clone $s->getCreatedAt())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
            'updatedAt' => (clone $s->getUpdatedAt())->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d\TH:i:s\Z'),
        ];
    }
}
