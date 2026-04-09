<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\RemoveUnitFromService;
use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;
use GlobalEmergency\Apuntate\Entity\User;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class RemoveUnitFromServiceTest extends TestCase
{
    private MockObject&ServiceRepositoryInterface $serviceRepository;
    private MockObject&UnitRepositoryInterface $unitRepository;
    private MockObject&GapRepositoryInterface $gapRepository;
    private RemoveUnitFromService $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->unitRepository = $this->createMock(UnitRepositoryInterface::class);
        $this->gapRepository = $this->createMock(GapRepositoryInterface::class);
        $this->useCase = new RemoveUnitFromService(
            $this->serviceRepository,
            $this->unitRepository,
            $this->gapRepository,
        );
    }

    public function testRemovesUnitAndGaps(): void
    {
        $unit = new Unit();
        $unit->setName('AMB-1');
        $unit->setIdentifier('AMB-1');

        $component = new Component();
        $component->setName('Driver');

        $uc = new UnitComponent();
        $uc->setUnit($unit);
        $uc->setComponent($component);
        $unit->addUnitComponent($uc);

        $service = $this->createService();
        $service->addUnit($unit);

        $gap = new Gap();
        $gap->setService($service);
        $gap->setUnitComponent($uc);
        $service->addGap($gap);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitRepository->method('findById')->willReturn($unit);
        $this->gapRepository->expects($this->once())->method('delete');
        $this->serviceRepository->expects($this->once())->method('save');

        $result = $this->useCase->execute(
            $service->getId()->toRfc4122(),
            $unit->getId()->toRfc4122(),
        );

        $this->assertFalse($result->getUnits()->contains($unit));
    }

    public function testRejectsRemovalWhenGapHasUser(): void
    {
        $unit = new Unit();
        $unit->setName('AMB-1');
        $unit->setIdentifier('AMB-1');

        $component = new Component();
        $component->setName('Driver');

        $uc = new UnitComponent();
        $uc->setUnit($unit);
        $uc->setComponent($component);
        $unit->addUnitComponent($uc);

        $service = $this->createService();
        $service->addUnit($unit);

        $user = new User();
        $user->setName('John');
        $user->setSurname('Doe');
        $user->setEmail('john@test.dev');
        $user->setPassword('hashed');
        $user->setDateStart(new \DateTime());

        $gap = new Gap();
        $gap->setService($service);
        $gap->setUnitComponent($uc);
        $gap->setUser($user);
        $service->addGap($gap);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitRepository->method('findById')->willReturn($unit);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Cannot remove unit: some gaps have assigned users.');

        $this->useCase->execute(
            $service->getId()->toRfc4122(),
            $unit->getId()->toRfc4122(),
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
}
