<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use GlobalEmergency\Apuntate\Entity\PasswordResetToken;
use GlobalEmergency\Apuntate\Entity\User;

interface PasswordResetTokenRepositoryInterface
{
    public function save(PasswordResetToken $token): void;

    public function findByHashedToken(string $hashedToken): ?PasswordResetToken;

    public function invalidateExistingTokensForUser(User $user): void;
}
