<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\RegisterOrganization;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegisterController extends AbstractController
{
    public function __construct(
        private RegisterOrganization $registerOrganization,
    ) {
    }

    #[Route('/api/auth/register', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $orgName = $data['org'] ?? '';
        $name = $data['uname'] ?? '';
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        if ('' === $orgName || '' === $name || '' === $email || '' === $password) {
            return new JsonResponse(
                ['error' => 'Organization, name, email and password are required.'],
                Response::HTTP_BAD_REQUEST,
            );
        }

        try {
            $result = $this->registerOrganization->execute($orgName, $name, $email, $password);
        } catch (\DomainException $e) {
            return new JsonResponse(
                ['error' => $e->getMessage()],
                Response::HTTP_CONFLICT,
            );
        }

        $user = $result['user'];
        $organization = $result['organization'];

        return new JsonResponse(
            [
                'id' => (string) $user->getId(),
                'email' => $user->getEmail(),
                'organization' => [
                    'id' => $organization->getId()->toRfc4122(),
                    'name' => $organization->getName(),
                    'slug' => $organization->getSlug(),
                ],
            ],
            Response::HTTP_CREATED,
        );
    }
}
