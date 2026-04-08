<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\GetUserProfile;
use GlobalEmergency\Apuntate\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/profile')]
final class ProfileController extends AbstractController
{
    public function __construct(
        private GetUserProfile $getUserProfile,
    ) {
    }

    #[Route('', methods: ['GET'])]
    public function getProfile(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return new JsonResponse($this->getUserProfile->execute($user));
    }
}
