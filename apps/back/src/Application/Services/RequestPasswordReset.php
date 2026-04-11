<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\PasswordResetToken;
use GlobalEmergency\Apuntate\Repository\PasswordResetTokenRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;

final class RequestPasswordReset
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private PasswordResetTokenRepositoryInterface $tokenRepository,
        private EmailSenderInterface $emailSender,
    ) {
    }

    public function execute(string $email): void
    {
        $user = $this->userRepository->findByEmail($email);
        if (null === $user) {
            return;
        }

        $this->tokenRepository->invalidateExistingTokensForUser($user);

        $plainToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $plainToken);

        $resetToken = new PasswordResetToken();
        $resetToken->setUser($user);
        $resetToken->setToken($hashedToken);
        $resetToken->setExpiresAt(new \DateTimeImmutable('+1 hour', new \DateTimeZone('UTC')));

        $this->tokenRepository->save($resetToken);

        try {
            $this->emailSender->sendPasswordResetEmail($user, $plainToken);
        } catch (\Throwable) {
            // Log silently — token is saved, user can retry
        }
    }
}
