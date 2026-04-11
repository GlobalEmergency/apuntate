<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Api\Infrastructure\Symfony;

use GlobalEmergency\Apuntate\Application\Services\EmailSenderInterface;
use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;

final readonly class SymfonyEmailSender implements EmailSenderInterface
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%app.frontend_url%')]
        private string $frontendUrl,
        #[Autowire('%app.mail_from%')]
        private string $mailFrom,
        #[Autowire('%app.mail_from_name%')]
        private string $mailFromName,
    ) {
    }

    public function sendWelcomeEmail(User $user, Organization $organization): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, $this->mailFromName))
            ->to($user->getEmail())
            ->subject('Bienvenido a Apúntate - '.$organization->getName())
            ->htmlTemplate('email/welcome.html.twig')
            ->context([
                'user' => $user,
                'organization' => $organization,
                'loginUrl' => $this->frontendUrl.'/login',
            ]);

        $this->mailer->send($email);
    }

    public function sendInvitationEmail(User $user, Organization $organization, string $plainPassword): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, $this->mailFromName))
            ->to($user->getEmail())
            ->subject('Te han invitado a '.$organization->getName().' en Apúntate')
            ->htmlTemplate('email/invitation.html.twig')
            ->context([
                'user' => $user,
                'organization' => $organization,
                'plainPassword' => $plainPassword,
                'loginUrl' => $this->frontendUrl.'/login',
            ]);

        $this->mailer->send($email);
    }

    public function sendPasswordResetEmail(User $user, string $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailFrom, $this->mailFromName))
            ->to($user->getEmail())
            ->subject('Recupera tu contraseña - Apúntate')
            ->htmlTemplate('email/password_reset.html.twig')
            ->context([
                'user' => $user,
                'resetUrl' => $this->frontendUrl.'/reset-password?token='.$resetToken,
            ]);

        $this->mailer->send($email);
    }
}
