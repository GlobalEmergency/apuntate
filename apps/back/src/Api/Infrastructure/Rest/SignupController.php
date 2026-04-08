<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\SignupForService;
use GlobalEmergency\Apuntate\Application\Services\WithdrawFromService;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SignupController extends AbstractController
{
    public function __construct(
        private SignupForService $signupForService,
        private WithdrawFromService $withdrawFromService,
        private GapRepositoryInterface $gapRepository,
    ) {
    }

    #[Route('/api/services/{serviceId}/signup', methods: ['POST'])]
    public function signup(string $serviceId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $gapId = $data['gap_id'] ?? null;

        try {
            $gap = $this->signupForService->execute($user, $serviceId, $gapId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse([
            'id' => $gap->getId()->toRfc4122(),
            'service_id' => $gap->getService()->getId()->toRfc4122(),
            'user' => $user->getName(),
        ], Response::HTTP_OK);
    }

    #[Route('/api/services/{serviceId}/withdraw', methods: ['POST'])]
    public function withdraw(string $serviceId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];
        $gapId = $data['gap_id'] ?? '';

        if ('' === $gapId) {
            return new JsonResponse(['error' => 'gap_id is required.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $this->withdrawFromService->execute($user, $gapId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/api/services/{serviceId}/gaps', methods: ['GET'])]
    public function gaps(string $serviceId): JsonResponse
    {
        $gaps = $this->gapRepository->findByService($serviceId);

        $result = array_map(function ($gap) {
            return [
                'id' => $gap->getId()->toRfc4122(),
                'component' => $gap->getUnitComponent()?->getComponent()?->getName(),
                'unit' => $gap->getUnitComponent()?->getUnit()?->getName(),
                'quantity' => $gap->getUnitComponent()?->getQuantity(),
                'user' => null !== $gap->getUser() ? [
                    'id' => $gap->getUser()->getId()->toRfc4122(),
                    'name' => $gap->getUser()->getName(),
                    'surname' => $gap->getUser()->getSurname(),
                ] : null,
            ];
        }, $gaps);

        return new JsonResponse($result);
    }
}
