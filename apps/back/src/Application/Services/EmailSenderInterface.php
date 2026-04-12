<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Application\Services;

use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\User;

interface EmailSenderInterface
{
    public function sendWelcomeEmail(User $user, Organization $organization): void;

    public function sendInvitationEmail(User $user, Organization $organization, string $activationToken): void;

    public function sendPasswordResetEmail(User $user, string $resetToken): void;
}
