<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\AddUnitToService;
use GlobalEmergency\Apuntate\Application\Services\CreateGaps;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AddUnitToServiceTest extends TestCase
{
    private MockObject&ServiceRepositoryInterface $serviceRepository;
    private MockObject&UnitRepositoryInterface $unitRepository;
    private CreateGaps $createGaps;
    private AddUnitToService $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->unitRepository = $this->createMock(UnitRepositoryInterface::class);
        $this->createGaps = new CreateGaps($this->unitRepository);
        $this->useCase = new AddUnitToService(
            $this->serviceRepository,
            $this->unitRepository,
            $this->createGaps,
        );
    }

    public function testAddsUnitToService(): void
    {
        $service = $this->createService();
        $unit = $this->createUnit();

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitRepository->method('findById')->willReturn($unit);
        $this->serviceRepository->expects($this->once())->method('save');

        $result = $this->useCase->execute(
            $service->getId()->toRfc4122(),
            $unit->getId()->toRfc4122(),
        );

        $this->assertTrue($result->getUnits()->contains($unit));
    }

    public function testRejectsServiceNotFound(): void
    {
        $this->serviceRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service not found.');

        $this->useCase->execute('nonexistent', 'unit-id');
    }

    public function testRejectsUnitNotFound(): void
    {
        $service = $this->createService();
        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unit not found.');

        $this->useCase->execute($service->getId()->toRfc4122(), 'nonexistent');
    }

    public function testRejectsDuplicateUnit(): void
    {
        $service = $this->createService();
        $unit = $this->createUnit();
        $service->addUnit($unit);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitRepository->method('findById')->willReturn($unit);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unit is already associated');

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

    private function createUnit(): Unit
    {
        $unit = new Unit();
        $unit->setName('Ambulance 1');
        $unit->setIdentifier('AMB-1');

        return $unit;
    }
}
