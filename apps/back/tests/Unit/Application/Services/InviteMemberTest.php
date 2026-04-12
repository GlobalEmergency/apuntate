<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\EmailSenderInterface;
use GlobalEmergency\Apuntate\Application\Services\InviteMember;
use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\OrganizationRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\PasswordResetTokenRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UserRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class InviteMemberTest extends TestCase
{
    private MockObject&OrganizationRepositoryInterface $organizationRepository;
    private MockObject&UserRepositoryInterface $userRepository;
    private MockObject&UserPasswordHasherInterface $passwordHasher;
    private MockObject&EmailSenderInterface $emailSender;
    private MockObject&PasswordResetTokenRepositoryInterface $tokenRepository;
    private InviteMember $useCase;

    protected function setUp(): void
    {
        $this->organizationRepository = $this->createMock(OrganizationRepositoryInterface::class);
        $this->userRepository = $this->createMock(UserRepositoryInterface::class);
        $this->passwordHasher = $this->createMock(UserPasswordHasherInterface::class);
        $this->emailSender = $this->createMock(EmailSenderInterface::class);
        $this->tokenRepository = $this->createMock(PasswordResetTokenRepositoryInterface::class);
        $this->useCase = new InviteMember(
            $this->organizationRepository,
            $this->userRepository,
            $this->passwordHasher,
            $this->emailSender,
            $this->tokenRepository,
        );
    }

    public function testInvitesNewUserAndSendsActivationEmail(): void
    {
        $organization = new Organization();
        $organization->setName('Test Org');
        $organization->setSlug('test-org');

        $this->organizationRepository->method('findById')->willReturn($organization);
        $this->userRepository->method('findByEmail')->willReturn(null);
        $this->passwordHasher->method('hashPassword')->willReturn('hashed');
        $this->userRepository->expects($this->once())->method('save');
        $this->organizationRepository->expects($this->once())->method('save');
        $this->tokenRepository->expects($this->once())->method('save');
        $this->emailSender->expects($this->once())->method('sendInvitationEmail');

        $result = $this->useCase->execute('org-id', 'new@example.com', 'New', 'User');

        $this->assertInstanceOf(OrganizationMember::class, $result);
        $this->assertEquals('member', $result->getRole());
    }

    public function testInvitesExistingUserWithoutActivationEmail(): void
    {
        $organization = new Organization();
        $organization->setName('Test Org');
        $organization->setSlug('test-org');
        $existingUser = new User();
        $existingUser->setEmail('existing@example.com');

        $this->organizationRepository->method('findById')->willReturn($organization);
        $this->userRepository->method('findByEmail')->willReturn($existingUser);
        $this->tokenRepository->expects($this->never())->method('save');
        $this->emailSender->expects($this->never())->method('sendInvitationEmail');

        $result = $this->useCase->execute('org-id', 'existing@example.com', 'Existing', 'User');

        $this->assertInstanceOf(OrganizationMember::class, $result);
    }

    public function testRejectsInvalidEmail(): void
    {
        $organization = new Organization();
        $this->organizationRepository->method('findById')->willReturn($organization);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('A valid email is required.');
        $this->useCase->execute('org-id', 'not-an-email', 'Test', 'User');
    }

    public function testRejectsEmptyName(): void
    {
        $organization = new Organization();
        $this->organizationRepository->method('findById')->willReturn($organization);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Name is required.');
        $this->useCase->execute('org-id', 'test@example.com', '', 'User');
    }

    public function testRejectsDuplicateMember(): void
    {
        $existingUser = new User();
        $existingUser->setEmail('member@example.com');

        $membership = new OrganizationMember();
        $membership->setUser($existingUser);

        $organization = new Organization();
        $organization->setName('Test Org');
        $organization->setSlug('test-org');
        $organization->addMember($membership);

        $this->organizationRepository->method('findById')->willReturn($organization);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('already a member');
        $this->useCase->execute('org-id', 'member@example.com', 'Test', 'User');
    }

    public function testRejectsNonExistentOrganization(): void
    {
        $this->organizationRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Organization not found.');
        $this->useCase->execute('bad-id', 'test@example.com', 'Test', 'User');
    }
}
