<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\MarkAlertAsRead;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\AlertRepositoryInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/alerts')]
final class AlertController extends AbstractController
{
    public function __construct(
        private AlertRepositoryInterface $alertRepository,
        private MarkAlertAsRead $markAlertAsRead,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function list(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $alerts = $this->alertRepository->findByUser($user);

        $result = array_map(fn ($alert) => [
            'id' => $alert->getId()->toRfc4122(),
            'title' => $alert->getTitle(),
            'resume' => $alert->getResume(),
            'type' => $alert->getType(),
            'show' => !$alert->isRead(),
            'service_id' => $alert->getService()?->getId()->toRfc4122(),
            'created_at' => $alert->getCreatedAt()?->format('c'),
        ], $alerts);

        return new JsonResponse($result);
    }

    #[Route('/{alertId}', methods: ['POST'])]
    public function markAsRead(string $alertId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        try {
            $this->markAlertAsRead->execute($user, $alertId);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
