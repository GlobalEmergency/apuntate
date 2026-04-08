<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\CreateGaps;
use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;
use GlobalEmergency\Apuntate\Repository\UnitRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CreateGapsTest extends TestCase
{
    private CreateGaps $createGaps;
    private UnitRepositoryInterface $unitRepository;

    protected function setUp(): void
    {
        $this->unitRepository = $this->createMock(UnitRepositoryInterface::class);
        $this->createGaps = new CreateGaps($this->unitRepository);
    }

    public function testCreatesGapsForUnitComponents(): void
    {
        $component = new Component();
        $component->setName('Technician');

        $unit = new Unit();
        $unit->setName('Ambulance');

        $unitComponent = new UnitComponent();
        $unitComponent->setUnit($unit);
        $unitComponent->setComponent($component);
        $unitComponent->setQuantity(2);
        $unit->addUnitComponent($unitComponent);

        $this->unitRepository->method('findById')->willReturn($unit);

        $service = new Service();
        $result = $this->createGaps->execute($service, [$unit->getId()->toRfc4122() => 2]);

        $this->assertCount(2, $result->getGaps());
    }

    public function testSkipsUnknownUnit(): void
    {
        $this->unitRepository->method('findById')->willReturn(null);

        $service = new Service();
        $result = $this->createGaps->execute($service, ['unknown-id' => 3]);

        $this->assertCount(0, $result->getGaps());
    }
}
