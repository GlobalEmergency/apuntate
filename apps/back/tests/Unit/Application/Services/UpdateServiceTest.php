<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\UpdateService;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpdateServiceTest extends TestCase
{
    private MockObject&ServiceRepositoryInterface $serviceRepository;
    private UpdateService $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->useCase = new UpdateService($this->serviceRepository);
    }

    public function testUpdatesServiceName(): void
    {
        $service = $this->createService();
        $this->serviceRepository->method('findById')->willReturn($service);
        $this->serviceRepository->expects($this->once())->method('save');

        $result = $this->useCase->execute(
            serviceId: $service->getId()->toRfc4122(),
            name: 'Updated Name',
        );

        $this->assertEquals('Updated Name', $result->getName());
    }

    public function testUpdatesServiceStatus(): void
    {
        $service = $this->createService();
        $this->serviceRepository->method('findById')->willReturn($service);

        $result = $this->useCase->execute(
            serviceId: $service->getId()->toRfc4122(),
            status: 'confirmed',
        );

        $this->assertEquals(ServiceStatus::CONFIRMED, $result->getStatus());
    }

    public function testRejectsEmptyName(): void
    {
        $service = $this->createService();
        $this->serviceRepository->method('findById')->willReturn($service);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service name cannot be empty.');

        $this->useCase->execute(serviceId: $service->getId()->toRfc4122(), name: '');
    }

    public function testRejectsNotFound(): void
    {
        $this->serviceRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service not found.');

        $this->useCase->execute(serviceId: 'nonexistent');
    }

    private function createService(): Service
    {
        $service = new Service();
        $service->setName('Original');
        $service->setDateStart(new \DateTimeImmutable('2026-05-01 09:00'));
        $service->setDateEnd(new \DateTimeImmutable('2026-05-01 14:00'));
        $service->setDatePlace(new \DateTimeImmutable('2026-05-01 08:00'));
        $service->setStatus(ServiceStatus::DRAFT);

        return $service;
    }
}
