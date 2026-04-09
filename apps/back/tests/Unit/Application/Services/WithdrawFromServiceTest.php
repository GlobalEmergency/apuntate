<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use Doctrine\ORM\EntityManagerInterface;
use GlobalEmergency\Apuntate\Application\Services\WithdrawFromService;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WithdrawFromServiceTest extends TestCase
{
    private MockObject&GapRepositoryInterface $gapRepository;
    private WithdrawFromService $useCase;

    protected function setUp(): void
    {
        $this->gapRepository = $this->createMock(GapRepositoryInterface::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('wrapInTransaction')->willReturnCallback(fn (callable $fn) => $fn());

        $this->useCase = new WithdrawFromService($this->gapRepository, $entityManager);
    }

    public function testWithdrawsUserFromGap(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $gap = new Gap();
        $gap->setUser($user);

        $this->gapRepository->method('findByIdForUpdate')->willReturn($gap);
        $this->gapRepository->expects($this->once())->method('save');

        $this->useCase->execute($user, $gap->getId()->toRfc4122());

        $this->assertNull($gap->getUser());
    }

    public function testRejectsWithdrawFromOtherUsersGap(): void
    {
        $owner = new User();
        $owner->setName('Owner');
        $owner->setEmail('owner@test.com');

        $gap = new Gap();
        $gap->setUser($owner);

        $this->gapRepository->method('findByIdForUpdate')->willReturn($gap);

        $otherUser = new User();
        $otherUser->setName('Other');
        $otherUser->setEmail('other@test.com');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('You are not signed up for this position.');

        $this->useCase->execute($otherUser, $gap->getId()->toRfc4122());
    }

    public function testRejectsWithdrawFromEmptyGap(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $gap = new Gap();

        $this->gapRepository->method('findByIdForUpdate')->willReturn($gap);

        $this->expectException(\DomainException::class);

        $this->useCase->execute($user, $gap->getId()->toRfc4122());
    }
}
