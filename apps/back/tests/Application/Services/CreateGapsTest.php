<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Tests\Application\Services;

use GlobalEmergency\Apuntate\Application\Services\CreateGaps;
use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;
use PHPUnit\Framework\TestCase;

class CreateGapsTest extends TestCase
{
    private CreateGaps $createGaps;

    protected function setUp(): void
    {
        $this->createGaps = new CreateGaps();
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

        $service = new Service();
        $this->createGaps->executeForUnit($service, $unit);

        $this->assertCount(2, $service->getGaps());
    }

    public function testCreatesGapsForMultipleComponents(): void
    {
        $comp1 = new Component();
        $comp1->setName('Driver');

        $comp2 = new Component();
        $comp2->setName('Medic');

        $unit = new Unit();
        $unit->setName('Ambulance');

        $uc1 = new UnitComponent();
        $uc1->setUnit($unit);
        $uc1->setComponent($comp1);
        $uc1->setQuantity(1);
        $unit->addUnitComponent($uc1);

        $uc2 = new UnitComponent();
        $uc2->setUnit($unit);
        $uc2->setComponent($comp2);
        $uc2->setQuantity(3);
        $unit->addUnitComponent($uc2);

        $service = new Service();
        $this->createGaps->executeForUnit($service, $unit);

        $this->assertCount(4, $service->getGaps());
    }
}
