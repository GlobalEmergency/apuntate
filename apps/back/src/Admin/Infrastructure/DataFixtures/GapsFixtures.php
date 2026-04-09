<?php

namespace GlobalEmergency\Apuntate\Admin\Infrastructure\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use GlobalEmergency\Apuntate\Entity\Service;

class GapsFixtures extends Fixture
{
    private ObjectManager $manager;

    public function load(ObjectManager $manager): void
    {
        $this->manager = $manager;

        for ($i = 0; $i < 10; ++$i) {
            $this->createService();
        }

        $this->manager->flush();
    }

    private function createService(): Service
    {
        $service = new Service();
        $service->setId(\Symfony\Component\Uid\Uuid::v4());
        $service->setStatus(\GlobalEmergency\Apuntate\Entity\ServiceStatus::CONFIRMED);

        $service->setName('Test Service '.rand(1, 100));
        $service->setDescription('Test Description');
        // Date start random nexts 5 days
        $date = new \DateTime();
        $date->modify('+'.rand(1, 5).' days');
        $service->setDateStart(clone $date);
        $date->modify('+'.rand(3, 12).' hours');
        $service->setDateEnd(clone $date);

        $service->setDatePlace((clone $service->getDateStart())->modify('-'.rand(30, 90).' minutes'));
        $this->manager->persist($service);

        return $service;
    }
}
