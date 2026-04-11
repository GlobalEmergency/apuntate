<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Rest;

use GlobalEmergency\Apuntate\Application\Services\ConfirmPasswordReset;
use GlobalEmergency\Apuntate\Application\Services\RequestPasswordReset;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/auth/password-reset', name: 'api_password_reset_')]
final class PasswordResetController extends AbstractController
{
    #[Route('/request', name: 'request', methods: ['POST'])]
    public function requestReset(Request $request, RequestPasswordReset $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? '';

        if ('' === trim($email)) {
            return new JsonResponse(['error' => 'El email es obligatorio.'], Response::HTTP_BAD_REQUEST);
        }

        $service->execute($email);

        return new JsonResponse(['message' => 'Si el email existe, recibirás un enlace de recuperación.']);
    }

    #[Route('/confirm', name: 'confirm', methods: ['POST'])]
    public function confirmReset(Request $request, ConfirmPasswordReset $service): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $token = $data['token'] ?? '';
        $password = $data['password'] ?? '';

        if ('' === trim($token) || '' === trim($password)) {
            return new JsonResponse(['error' => 'Token y contraseña son obligatorios.'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $service->execute($token, $password);
        } catch (\DomainException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['message' => 'Contraseña actualizada correctamente.']);
    }
}
