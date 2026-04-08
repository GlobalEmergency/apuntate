<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\SignupForService;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use PHPUnit\Framework\TestCase;

class SignupForServiceTest extends TestCase
{
    private GapRepositoryInterface $gapRepository;
    private SignupForService $useCase;

    protected function setUp(): void
    {
        $this->gapRepository = $this->createMock(GapRepositoryInterface::class);
        $this->useCase = new SignupForService($this->gapRepository);
    }

    public function testSignsUpUserForSpecificGap(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $gap = new Gap();
        $service = new Service();
        $gap->setService($service);

        $this->gapRepository->method('findById')->willReturn($gap);
        $this->gapRepository->expects($this->once())->method('save');

        $result = $this->useCase->execute($user, 'service-id', $gap->getId()->toRfc4122());

        $this->assertSame($user, $result->getUser());
    }

    public function testSignsUpUserForFirstAvailableGap(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $gap = new Gap();
        $service = new Service();
        $gap->setService($service);

        $this->gapRepository->method('findAvailableByService')->willReturn([$gap]);
        $this->gapRepository->expects($this->once())->method('save');

        $result = $this->useCase->execute($user, $service->getId()->toRfc4122());

        $this->assertSame($user, $result->getUser());
    }

    public function testRejectsSignupWhenGapAlreadyTaken(): void
    {
        $existingUser = new User();
        $existingUser->setName('Existing');
        $existingUser->setEmail('existing@test.com');

        $gap = new Gap();
        $gap->setUser($existingUser);

        $this->gapRepository->method('findById')->willReturn($gap);

        $newUser = new User();
        $newUser->setName('New');
        $newUser->setEmail('new@test.com');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('This position is already taken.');

        $this->useCase->execute($newUser, 'service-id', $gap->getId()->toRfc4122());
    }

    public function testRejectsSignupWhenNoAvailableGaps(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setEmail('john@test.com');

        $this->gapRepository->method('findAvailableByService')->willReturn([]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('No available positions for this service.');

        $this->useCase->execute($user, 'service-id');
    }
}
