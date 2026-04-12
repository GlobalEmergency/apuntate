<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\EmailSenderInterface;
use GlobalEmergency\Apuntate\Application\Services\RegisterOrganization;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegisterOrganizationTest extends TestCase
{
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&UserPasswordHasherInterface $passwordHasher;
    private MockObject&OrganizationRepositoryInterface $organizationRepository;
    private MockObject&EmailSenderInterface $emailSender;
    private RegisterOrganization $useCase;

    protected function setUp(): void
    {
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->organizationRepository = $this->createMock(OrganizationRepositoryInterface::class);
        $this->emailSender = $this->createMock(EmailSenderInterface::class);
        $this->useCase = new RegisterOrganization(
            $this->userRepository,
            $this->passwordHasher,
            $this->organizationRepository,
            $this->emailSender,
        );
    }

    public function testRegistersNewOrganizationWithAdminUser(): void
    {
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed_password');
        $this->userRepository->expects($this->once())->method('save');
        $this->organizationRepository->expects($this->once())->method('save');

        $result = $this->useCase->execute('Civil Protection Madrid', 'John', 'john@example.com', 'secret123');

        $this->assertArrayHasKey('user', $result);
        $this->assertArrayHasKey('organization', $result);

        $user = $result['user'];
        $org = $result['organization'];

        $this->assertEquals('John', $user->getName());
        $this->assertEquals('john@example.com', $user->getEmail());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());

        $this->assertEquals('Civil Protection Madrid', $org->getName());
        $this->assertEquals('civil-protection-madrid', $org->getSlug());
        $this->assertCount(1, $org->getMembers());
    }

    public function testRejectsDuplicateEmail(): void
    {
        $existingUser = new User();
        $this->userRepository->method('findByEmail')->willReturn($existingUser);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A user with this email already exists.');

        $this->useCase->execute('Test Org', 'John', 'john@example.com', 'secret123');
    }
}
