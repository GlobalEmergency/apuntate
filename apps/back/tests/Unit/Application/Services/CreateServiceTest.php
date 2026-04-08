<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Unit\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\CreateService;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Repository\ServiceRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateServiceTest extends TestCase
{
    private ServiceRepositoryInterface $serviceRepository;
    private CreateService $useCase;

    protected function setUp(): void
    {
        $this->serviceRepository = $this->createMock(ServiceRepositoryInterface::class);
        $this->useCase = new CreateService($this->serviceRepository);
    }

    public function testCreatesServiceWithValidData(): void
    {
        $this->serviceRepository->expects($this->once())->method('save');

        $service = $this->useCase->execute(
            name: 'Children Race Coverage',
            dateStart: new \DateTimeImmutable('2026-05-01 09:00'),
            dateEnd: new \DateTimeImmutable('2026-05-01 14:00'),
            datePlace: new \DateTimeImmutable('2026-05-01 08:00'),
            description: 'Annual kids race in the park',
        );

        $this->assertEquals('Children Race Coverage', $service->getName());
        $this->assertEquals('Annual kids race in the park', $service->getDescription());
        $this->assertEquals(ServiceStatus::DRAFT, $service->getStatus());
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Service name is required.');

        $this->useCase->execute(
            name: '',
            dateStart: new \DateTimeImmutable('2026-05-01 09:00'),
            dateEnd: new \DateTimeImmutable('2026-05-01 14:00'),
            datePlace: new \DateTimeImmutable('2026-05-01 08:00'),
        );
    }

    public function testRejectsEndBeforeStart(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('End date must be after start date.');

        $this->useCase->execute(
            name: 'Test Service',
            dateStart: new \DateTimeImmutable('2026-05-01 14:00'),
            dateEnd: new \DateTimeImmutable('2026-05-01 09:00'),
            datePlace: new \DateTimeImmutable('2026-05-01 08:00'),
        );
    }
}
