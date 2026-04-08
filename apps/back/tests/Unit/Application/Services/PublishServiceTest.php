<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\NotifyNewService;
use GlobalEmergency\Apuntate\Application\Services\PublishService;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use PHPUnit\Framework\TestCase;

class PublishServiceTest extends TestCase
{
    private ServiceRepositoryInterface $serviceRepository;
    private NotifyNewService $notifyNewService;
    private PublishService $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->notifyNewService = $this->createMock(NotifyNewService::class);
        $this->useCase = new PublishService($this->serviceRepository, $this->notifyNewService);
    }

    public function testPublishesDraftService(): void
    {
        $service = new Service();
        $service->setName('Test');
        $service->setStatus(ServiceStatus::DRAFT);

        $this->serviceRepository->method('findById')->willReturn($service);
        $this->serviceRepository->expects($this->once())->method('save');
        $this->notifyNewService->expects($this->once())->method('execute');

        $result = $this->useCase->execute($service->getId()->toRfc4122());

        $this->assertEquals(ServiceStatus::CONFIRMED, $result->getStatus());
    }

    public function testRejectsPublishingNonDraftService(): void
    {
        $service = new Service();
        $service->setName('Test');
        $service->setStatus(ServiceStatus::CONFIRMED);

        $this->serviceRepository->method('findById')->willReturn($service);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Only draft services can be published.');

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
