<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Repository\PasswordResetTokenRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class ConfirmPasswordReset
{
    public function __construct(
        private PasswordResetTokenRepositoryInterface $tokenRepository,
        private UserRepositoryInterface $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function execute(string $plainToken, string $newPassword): void
    {
        $hashedToken = hash('sha256', $plainToken);
        $resetToken = $this->tokenRepository->findByHashedToken($hashedToken);

        if (null === $resetToken || $resetToken->isUsed() || $resetToken->isExpired()) {
            throw new \DomainException('El enlace de recuperación no es válido o ha expirado.');
        }

        if (\strlen($newPassword) < 6) {
            throw new \DomainException('La contraseña debe tener al menos 6 caracteres.');
        }

        $user = $resetToken->getUser();
        $user->setPassword($this->passwordHasher->hashPassword($user, $newPassword));
        $resetToken->markAsUsed();

        $this->tokenRepository->save($resetToken);
        $this->userRepository->save($user);
    }
}
