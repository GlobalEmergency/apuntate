<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\RemoveGap;
use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveGapTest extends TestCase
{
    private MockObject&ServiceRepositoryInterface $serviceRepository;
    private MockObject&GapRepositoryInterface $gapRepository;
    private RemoveGap $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->gapRepository = $this->createMock(GapRepositoryInterface::class);
        $this->useCase = new RemoveGap($this->serviceRepository, $this->gapRepository);
    }

    public function testRemovesUnassignedGap(): void
    {
        $service = $this->createService();
        $gap = $this->createGap($service);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->gapRepository->method('findById')->willReturn($gap);
        $this->gapRepository->expects($this->once())->method('delete');

        $this->useCase->execute(
            $service->getId()->toRfc4122(),
            $gap->getId()->toRfc4122(),
        );

        $this->assertFalse($service->getGaps()->contains($gap));
    }

    public function testRejectsGapWithAssignedUser(): void
    {
        $service = $this->createService();
        $gap = $this->createGap($service);

        $user = new User();
        $user->setName('John');
        $user->setSurname('Doe');
        $user->setEmail('john@test.dev');
        $user->setPassword('hashed');
        $user->setDateStart(new \DateTime());
        $gap->setUser($user);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->gapRepository->method('findById')->willReturn($gap);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot remove a gap with an assigned user.');

        $this->useCase->execute(
            $service->getId()->toRfc4122(),
            $gap->getId()->toRfc4122(),
        );
    }

    public function testRejectsGapFromDifferentService(): void
    {
        $service1 = $this->createService();
        $service2 = $this->createService();
        $gap = $this->createGap($service2);

        $this->serviceRepository->method('findById')->willReturn($service1);
        $this->gapRepository->method('findById')->willReturn($gap);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Gap does not belong to this service.');

        $this->useCase->execute(
            $service1->getId()->toRfc4122(),
            $gap->getId()->toRfc4122(),
        );
    }

    private function createService(): Service
    {
        $service = new Service();
        $service->setName('Test Service');
        $service->setDateStart(new \DateTimeImmutable('2026-05-01 09:00'));
        $service->setDateEnd(new \DateTimeImmutable('2026-05-01 14:00'));
        $service->setDatePlace(new \DateTimeImmutable('2026-05-01 08:00'));
        $service->setStatus(ServiceStatus::DRAFT);

        return $service;
    }

    private function createGap(Service $service): Gap
    {
        $uc = new UnitComponent();
        $uc->setUnit((new Unit())->setName('AMB')->setIdentifier('AMB'));
        $uc->setComponent((new Component())->setName('Driver'));

        $gap = new Gap();
        $gap->setService($service);
        $gap->setUnitComponent($uc);
        $service->addGap($gap);

        return $gap;
    }
}
