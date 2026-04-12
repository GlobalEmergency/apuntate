<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\ConfirmPasswordReset;
use GlobalEmergency\Apuntate\Entity\PasswordResetToken;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\PasswordResetTokenRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ConfirmPasswordResetTest extends TestCase
{
    private MockObject&PasswordResetTokenRepositoryInterface $tokenRepository;
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&UserPasswordHasherInterface $passwordHasher;
    private ConfirmPasswordReset $useCase;

    protected function setUp(): void
    {
        $this->tokenRepository = $this->createMock(PasswordResetTokenRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->useCase = new ConfirmPasswordReset(
            $this->tokenRepository,
            $this->userRepository,
            $this->passwordHasher,
        );
    }

    public function testResetsPasswordWithValidToken(): void
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('old_hash');

        $plainToken = bin2hex(random_bytes(32));
        $hashedToken = hash('sha256', $plainToken);

        $token = new PasswordResetToken();
        $token->setUser($user);
        $token->setToken($hashedToken);
        $token->setExpiresAt(new \DateTimeImmutable('+1 hour', new \DateTimeZone('UTC')));

        $this->tokenRepository->method('findByHashedToken')->with($hashedToken)->willReturn($token);
        $this->passwordHasher->method('hashPassword')->willReturn('new_hash');
        $this->tokenRepository->expects($this->once())->method('save');
        $this->userRepository->expects($this->once())->method('save');

        $this->useCase->execute($plainToken, 'newpassword');

        $this->assertTrue($token->isUsed());
        $this->assertEquals('new_hash', $user->getPassword());
    }

    public function testRejectsInvalidToken(): void
    {
        $this->tokenRepository->method('findByHashedToken')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->useCase->execute('invalid_token', 'newpassword');
    }

    public function testRejectsExpiredToken(): void
    {
        $user = new User();
        $token = new PasswordResetToken();
        $token->setUser($user);
        $token->setToken(hash('sha256', 'expired'));
        $token->setExpiresAt(new \DateTimeImmutable('-1 hour', new \DateTimeZone('UTC')));

        $this->tokenRepository->method('findByHashedToken')->willReturn($token);

        $this->expectException(\DomainException::class);
        $this->useCase->execute('expired', 'newpassword');
    }

    public function testRejectsUsedToken(): void
    {
        $user = new User();
        $token = new PasswordResetToken();
        $token->setUser($user);
        $token->setToken(hash('sha256', 'used'));
        $token->setExpiresAt(new \DateTimeImmutable('+1 hour', new \DateTimeZone('UTC')));
        $token->markAsUsed();

        $this->tokenRepository->method('findByHashedToken')->willReturn($token);

        $this->expectException(\DomainException::class);
        $this->useCase->execute('used', 'newpassword');
    }

    public function testRejectsShortPassword(): void
    {
        $user = new User();
        $token = new PasswordResetToken();
        $token->setUser($user);
        $token->setToken(hash('sha256', 'valid'));
        $token->setExpiresAt(new \DateTimeImmutable('+1 hour', new \DateTimeZone('UTC')));

        $this->tokenRepository->method('findByHashedToken')->willReturn($token);

        $this->expectException(\DomainException::class);
        $this->useCase->execute('valid', '12345');
    }
}
