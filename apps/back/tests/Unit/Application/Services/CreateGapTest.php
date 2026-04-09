<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\CreateGap;
use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;
use GlobalEmergency\Apuntate\Repository\GapRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use GlobalEmergency\Apuntate\Repository\UnitComponentRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateGapTest extends TestCase
{
    private ServiceRepositoryInterface $serviceRepository;
    private UnitComponentRepositoryInterface $unitComponentRepository;
    private GapRepositoryInterface $gapRepository;
    private CreateGap $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->unitComponentRepository = $this->createMock(UnitComponentRepositoryInterface::class);
        $this->gapRepository = $this->createMock(GapRepositoryInterface::class);
        $this->useCase = new CreateGap(
            $this->serviceRepository,
            $this->unitComponentRepository,
            $this->gapRepository,
        );
    }

    public function testCreatesGap(): void
    {
        $service = $this->createService();
        $uc = $this->createUnitComponent();

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitComponentRepository->method('findById')->willReturn($uc);
        $this->gapRepository->expects($this->once())->method('save');

        $gap = $this->useCase->execute(
            $service->getId()->toRfc4122(),
            $uc->getId()->toRfc4122(),
        );

        $this->assertSame($service, $gap->getService());
        $this->assertSame($uc, $gap->getUnitComponent());
        $this->assertNull($gap->getUser());
    }

    public function testRejectsServiceNotFound(): void
    {
        $this->serviceRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service not found.');

        $this->useCase->execute('nonexistent', 'uc-id');
    }

    public function testRejectsUnitComponentNotFound(): void
    {
        $service = $this->createService();
        $this->serviceRepository->method('findById')->willReturn($service);
        $this->unitComponentRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Unit component not found.');

        $this->useCase->execute($service->getId()->toRfc4122(), 'nonexistent');
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

    private function createUnitComponent(): UnitComponent
    {
        $unit = new Unit();
        $unit->setName('AMB-1');
        $unit->setIdentifier('AMB-1');

        $component = new Component();
        $component->setName('Driver');

        $uc = new UnitComponent();
        $uc->setUnit($unit);
        $uc->setComponent($component);

        return $uc;
    }
}
