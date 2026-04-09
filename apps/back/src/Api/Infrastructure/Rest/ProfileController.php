<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\GetUserProfile;
use GlobalEmergency\Apuntate\Application\Services\UpdateMyProfile;
use GlobalEmergency\Apuntate\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

    #[Route('', methods: ['PUT'])]
    public function updateProfile(Request $request, UpdateMyProfile $updateMyProfile): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        $data = json_decode($request->getContent(), true) ?? [];

        try {
            $updatedUser = $updateMyProfile->execute(
                $user,
                $data['name'] ?? null,
                $data['surname'] ?? null,
            );
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
            'name' => $updatedUser->getName(),
            'surname' => $updatedUser->getSurname(),
            'email' => $updatedUser->getEmail(),
        ]);
    }
}
