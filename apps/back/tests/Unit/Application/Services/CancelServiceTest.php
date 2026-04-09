<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\CancelService;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CancelServiceTest extends TestCase
{
    private MockObject&ServiceRepositoryInterface $serviceRepository;
    private CancelService $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->useCase = new CancelService($this->serviceRepository);
    }

    public function testCancelsActiveService(): void
    {
        $service = new Service();
        $service->setName('Test');
        $service->setStatus(ServiceStatus::CONFIRMED);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->serviceRepository->expects($this->once())->method('save');

        $this->useCase->execute($service->getId()->toRfc4122());

        $this->assertEquals(ServiceStatus::CANCELLED, $service->getStatus());
    }

    public function testRejectsCancellingAlreadyCancelledService(): void
    {
        $service = new Service();
        $service->setName('Test');
        $service->setStatus(ServiceStatus::CANCELLED);

        $this->serviceRepository->method('findById')->willReturn($service);

        $this->expectException(\DomainException::class);

        $this->useCase->execute($service->getId()->toRfc4122());
    }

    public function testRejectsCancellingFinishedService(): void
    {
        $service = new Service();
        $service->setName('Test');
        $service->setStatus(ServiceStatus::FINISHED);

        $this->serviceRepository->method('findById')->willReturn($service);

        $this->expectException(\DomainException::class);

        $this->useCase->execute($service->getId()->toRfc4122());
    }

    public function testRejectsNotFound(): void
    {
        $this->serviceRepository->method('findById')->willReturn(null);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service not found.');

        $this->useCase->execute('nonexistent');
    }
}
