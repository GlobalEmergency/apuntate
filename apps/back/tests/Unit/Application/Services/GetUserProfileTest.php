<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\GetUserProfile;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class GetUserProfileTest extends TestCase
{
    private MockObject&GapRepositoryInterface $gapRepository;
    private GetUserProfile $useCase;

    protected function setUp(): void
    {
        $this->gapRepository = $this->createMock(GapRepositoryInterface::class);
        $this->useCase = new GetUserProfile($this->gapRepository);
    }

    public function testReturnsProfileWithStats(): void
    {
        $user = new User();
        $user->setName('John');
        $user->setSurname('Doe');
        $user->setEmail('john@test.com');
        $user->setDateStart(new \DateTime());
        $user->setRoles(['ROLE_ADMIN']);

        $service = new Service();
        $service->setName('Test');
        $service->setDateStart(new \DateTimeImmutable('2026-05-01 09:00'));
        $service->setDateEnd(new \DateTimeImmutable('2026-05-01 14:00'));

        $gap = new Gap();
        $gap->setService($service);
        $gap->setUser($user);

        $this->gapRepository->method('findCompletedByUser')->willReturn([$gap]);

        $result = $this->useCase->execute($user);

        $this->assertEquals('John', $result['name']);
        $this->assertEquals('john@test.com', $result['email']);
        $this->assertEquals(1, $result['stats']['totalServices']);
        $this->assertEquals(5.0, $result['stats']['totalHours']);
    }

    public function testReturnsZeroStatsForNewUser(): void
    {
        $user = new User();
        $user->setName('New');
        $user->setSurname('User');
        $user->setEmail('new@test.com');
        $user->setDateStart(new \DateTime());

        $this->gapRepository->method('findCompletedByUser')->willReturn([]);

        $result = $this->useCase->execute($user);

        $this->assertEquals(0, $result['stats']['totalServices']);
        $this->assertEquals(0, $result['stats']['totalHours']);
    }
}
