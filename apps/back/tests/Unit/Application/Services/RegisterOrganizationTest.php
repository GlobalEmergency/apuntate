<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\RegisterOrganization;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterOrganizationTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private UserPasswordHasherInterface $passwordHasher;
    private RegisterOrganization $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->useCase = new RegisterOrganization($this->userRepository, $this->passwordHasher);
    }

    public function test_registers_new_user_with_admin_role(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed_password');

        $this->userRepository->expects($this->once())->method('save');

        $user = $this->useCase->execute('John', 'john@example.com', 'secret123');

        $this->assertEquals('John', $user->getName());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertEquals('hashed_password', $user->getPassword());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function test_rejects_duplicate_email(): void
    {
        $existingUser = new User();
        $this->userRepository->method('findByEmail')->willReturn($existingUser);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A user with this email already exists.');

        $this->useCase->execute('John', 'john@example.com', 'secret123');
    }
}
