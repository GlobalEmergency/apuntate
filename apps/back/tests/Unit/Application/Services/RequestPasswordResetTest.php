<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\EmailSenderInterface;
use GlobalEmergency\Apuntate\Application\Services\RequestPasswordReset;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\PasswordResetTokenRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class RequestPasswordResetTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&PasswordResetTokenRepositoryInterface $tokenRepository;
    private MockObject&EmailSenderInterface $emailSender;
    private MockObject&LoggerInterface $logger;
    private RequestPasswordReset $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->tokenRepository = $this->createMock(PasswordResetTokenRepositoryInterface::class);
        $this->emailSender = $this->createMock(EmailSenderInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->useCase = new RequestPasswordReset(
            $this->userRepository,
            $this->tokenRepository,
            $this->emailSender,
            $this->logger,
        );
    }

    public function testCreatesTokenAndSendsEmail(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->userRepository->method('findByEmail')->with('test@example.com')->willReturn($user);
        $this->tokenRepository->expects($this->once())->method('invalidateExistingTokensForUser')->with($user);
        $this->tokenRepository->expects($this->once())->method('save');
        $this->emailSender->expects($this->once())->method('sendPasswordResetEmail');

        $this->useCase->execute('test@example.com');
    }

    public function testDoesNothingForUnknownEmail(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->tokenRepository->expects($this->never())->method('save');
        $this->emailSender->expects($this->never())->method('sendPasswordResetEmail');

        $this->useCase->execute('unknown@example.com');
    }

    public function testLogsErrorOnEmailFailure(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');

        $this->userRepository->method('findByEmail')->willReturn($user);
        $this->emailSender->method('sendPasswordResetEmail')->willThrowException(new \RuntimeException('SMTP down'));
        $this->logger->expects($this->once())->method('error');

        $this->useCase->execute('test@example.com');
    }
}
