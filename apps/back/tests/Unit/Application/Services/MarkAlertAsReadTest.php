<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\MarkAlertAsRead;
use GlobalEmergency\Apuntate\Entity\Alert;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\AlertRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MarkAlertAsReadTest extends TestCase
{
    private MockObject&AlertRepositoryInterface $alertRepository;
    private MarkAlertAsRead $useCase;

    protected function setUp(): void
    {
        $this->alertRepository = $this->createMock(AlertRepositoryInterface::class);
        $this->useCase = new MarkAlertAsRead($this->alertRepository);
    }

    public function testMarksAlertAsRead(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $alert = new Alert();
        $alert->setTitle('Test');
        $alert->setResume('Test resume');
        $alert->setType('new_service');
        $alert->setRecipient($user);

        $this->alertRepository->method('findById')->willReturn($alert);
        $this->alertRepository->expects($this->once())->method('save');

        $this->useCase->execute($user, $alert->getId()->toRfc4122());

        $this->assertTrue($alert->isRead());
    }

    public function testRejectsAlertBelongingToOtherUser(): void
    {
        $owner = new User();
        $owner->setName('Owner');
        $owner->setEmail('owner@test.com');

        $alert = new Alert();
        $alert->setTitle('Test');
        $alert->setResume('Test resume');
        $alert->setType('new_service');
        $alert->setRecipient($owner);

        $this->alertRepository->method('findById')->willReturn($alert);

        $otherUser = new User();
        $otherUser->setName('Other');
        $otherUser->setEmail('other@test.com');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This alert does not belong to you.');

        $this->useCase->execute($otherUser, $alert->getId()->toRfc4122());
    }

    public function testRejectsNotFound(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $this->alertRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Alert not found.');

        $this->useCase->execute($user, 'nonexistent');
    }
}
