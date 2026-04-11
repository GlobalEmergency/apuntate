<?php

namespace GlobalEmergency\Apuntate\Command;

use Doctrine\ORM\EntityManagerInterface;
use GlobalEmergency\Apuntate\Entity\Component;
use GlobalEmergency\Apuntate\Entity\Gap;
use GlobalEmergency\Apuntate\Entity\Organization;
use GlobalEmergency\Apuntate\Entity\OrganizationMember;
use GlobalEmergency\Apuntate\Entity\Requirement;
use GlobalEmergency\Apuntate\Entity\Service;
use GlobalEmergency\Apuntate\Entity\ServiceStatus;
use GlobalEmergency\Apuntate\Entity\Speciality;
use GlobalEmergency\Apuntate\Entity\Unit;
use GlobalEmergency\Apuntate\Entity\UnitComponent;
use GlobalEmergency\Apuntate\Entity\User;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:seed-demo',
    description: 'Seed demo account with two organizations and realistic content',
)]
class SeedDemoDataCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $existing = $this->em->getRepository(User::class)->findOneBy(['email' => 'admin@demo.com']);
        if (null !== $existing) {
            $io->warning('User admin@demo.com already exists. Skipping.');

            return Command::SUCCESS;
        }

        $io->section('Creating demo user');
        $user = $this->createDemoUser();

        $io->section('Creating specialities');
        $specialities = $this->createSpecialities();

        $io->section('Creating requirements');
        $requirements = $this->createRequirements();

        $io->section('Creating components');
        $components = $this->createComponents($requirements);

        $io->section('Creating Organization 1: Protección Civil Marbella');
        $org1 = $this->createOrganization1($user, $specialities, $components);

        $io->section('Creating Organization 2: Cruz Roja Málaga');
        $org2 = $this->createOrganization2($user, $specialities, $components);

        $this->em->flush();

        $io->success(sprintf(
            'Demo data created: user admin@demo.com, orgs "%s" and "%s"',
            $org1->getName(),
            $org2->getName(),
        ));

        return Command::SUCCESS;
    }

    private function createDemoUser(): User
    {
        $user = new User();
        $user->setName('Admin');
        $user->setSurname('Demo');
        $user->setEmail('admin@demo.com');
        $user->setDateStart(new \DateTime('now', new \DateTimeZone('UTC')));
        $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
        $user->setRoles(['ROLE_ADMIN']);
        $this->em->persist($user);

        return $user;
    }

    /** @return Speciality[] */
    private function createSpecialities(): array
    {
        $data = [
            ['Sanitarios', 'SAN'],
            ['Rescate', 'RES'],
            ['Logística', 'LOG'],
            ['Comunicaciones', 'COM'],
            ['Transporte', 'TRA'],
        ];

        $specialities = [];
        foreach ($data as [$name, $abbr]) {
            $s = new Speciality();
            $s->setName($name);
            $s->setAbbreviation($abbr);
            $this->em->persist($s);
            $specialities[$abbr] = $s;
        }

        return $specialities;
    }

    /** @return Requirement[] */
    private function createRequirements(): array
    {
        $names = [
            'Permiso de conducir B',
            'Título de socorrista',
            'Curso de primeros auxilios',
            'Formación en radiocomunicaciones',
            'Curso de rescate en altura',
        ];

        $requirements = [];
        foreach ($names as $name) {
            $r = new Requirement();
            $r->setName($name);
            $this->em->persist($r);
            $requirements[] = $r;
        }

        return $requirements;
    }

    /**
     * @param Requirement[] $requirements
     *
     * @return Component[]
     */
    private function createComponents(array $requirements): array
    {
        $data = [
            'Jefe de Equipo' => [0],
            'Socorrista' => [1, 2],
            'Conductor' => [0],
            'Operador de Radio' => [3],
            'Rescatador' => [2, 4],
            'Auxiliar Logístico' => [],
            'Camillero' => [2],
        ];

        $components = [];
        foreach ($data as $name => $reqIndexes) {
            $c = new Component();
            $c->setName($name);
            foreach ($reqIndexes as $i) {
                $c->addRequirement($requirements[$i]);
            }
            $this->em->persist($c);
            $components[$name] = $c;
        }

        return $components;
    }

    /**
     * @param Speciality[] $specialities
     * @param Component[]  $components
     */
    private function createOrganization1(User $user, array $specialities, array $components): Organization
    {
        $org = new Organization();
        $org->setName('Protección Civil Marbella');
        $org->setSlug('proteccion-civil-marbella');
        $org->setDescription('Agrupación de Voluntarios de Protección Civil del Ayuntamiento de Marbella');

        $membership = new OrganizationMember();
        $membership->setUser($user);
        $membership->setOrganization($org);
        $membership->setRole(OrganizationMember::ROLE_ADMIN);
        $org->addMember($membership);

        $this->em->persist($user);
        $this->em->persist($org);

        // Members
        $members = $this->createOrgMembers($org, [
            ['Carlos', 'García', 'member'],
            ['María', 'López', 'member'],
            ['Pedro', 'Martínez', 'manager'],
            ['Ana', 'Fernández', 'member'],
            ['Luis', 'Sánchez', 'member'],
            ['Elena', 'Torres', 'member'],
        ]);

        // Units
        $units = [];

        $units[] = $this->createUnit($org, 'Ambulancia SVB-1', 'AMB-01', $specialities['SAN'], [
            ['Conductor', 1],
            ['Socorrista', 2],
        ], $components);

        $units[] = $this->createUnit($org, 'Ambulancia SVB-2', 'AMB-02', $specialities['SAN'], [
            ['Conductor', 1],
            ['Socorrista', 2],
        ], $components);

        $units[] = $this->createUnit($org, 'Vehículo de Mando', 'VM-01', $specialities['COM'], [
            ['Jefe de Equipo', 1],
            ['Operador de Radio', 1],
        ], $components);

        $units[] = $this->createUnit($org, 'Equipo de Rescate', 'RES-01', $specialities['RES'], [
            ['Jefe de Equipo', 1],
            ['Rescatador', 3],
            ['Camillero', 2],
        ], $components);

        $units[] = $this->createUnit($org, 'Puesto Logístico', 'LOG-01', $specialities['LOG'], [
            ['Jefe de Equipo', 1],
            ['Auxiliar Logístico', 3],
        ], $components);

        // Services
        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        // Past finished services
        for ($i = 1; $i <= 3; ++$i) {
            $start = (clone $now)->modify("-{$i} weeks")->setTime(8, 0);
            $this->createService(
                $org,
                "Dispositivo Playa Semana {$i}",
                "Cobertura sanitaria de playas - turno de mañana. Semana {$i}.",
                $start,
                (clone $start)->modify('+8 hours'),
                (clone $start)->modify('-30 minutes'),
                ServiceStatus::FINISHED,
                [$units[0], $units[2]],
                $members,
            );
        }

        // Active confirmed services
        $start = (clone $now)->modify('+1 day')->setTime(9, 0);
        $this->createService(
            $org,
            'Carrera Popular San Pedro',
            'Dispositivo sanitario para carrera popular 10km. Punto de inicio en Bulevar.',
            $start,
            (clone $start)->modify('+6 hours'),
            (clone $start)->modify('-45 minutes'),
            ServiceStatus::CONFIRMED,
            [$units[0], $units[1], $units[3]],
            $members,
        );

        $start = (clone $now)->modify('+3 days')->setTime(20, 0);
        $this->createService(
            $org,
            'Concierto Starlite',
            'Cobertura sanitaria evento nocturno Starlite Festival. Aforo 3000 personas.',
            $start,
            (clone $start)->modify('+5 hours'),
            (clone $start)->modify('-60 minutes'),
            ServiceStatus::CONFIRMED,
            [$units[0], $units[1], $units[2], $units[3]],
            $members,
        );

        $start = (clone $now)->modify('+5 days')->setTime(7, 0);
        $this->createService(
            $org,
            'Triatlón Costa del Sol',
            'Dispositivo completo para triatlón. Natación, ciclismo y carrera a pie.',
            $start,
            (clone $start)->modify('+10 hours'),
            (clone $start)->modify('-90 minutes'),
            ServiceStatus::CONFIRMED,
            [$units[0], $units[1], $units[3], $units[4]],
            $members,
        );

        // Draft services
        $start = (clone $now)->modify('+10 days')->setTime(10, 0);
        $this->createService(
            $org,
            'Feria de San Bernabé',
            'Dispositivo preventivo para feria municipal. Pendiente de confirmación.',
            $start,
            (clone $start)->modify('+12 hours'),
            (clone $start)->modify('-60 minutes'),
            ServiceStatus::DRAFT,
            [$units[0]],
            [],
        );

        $start = (clone $now)->modify('+2 weeks')->setTime(8, 0);
        $this->createService(
            $org,
            'Simulacro Terremoto',
            'Ejercicio de simulación sísmica en zona residencial. Coordinado con bomberos.',
            $start,
            (clone $start)->modify('+4 hours'),
            (clone $start)->modify('-30 minutes'),
            ServiceStatus::DRAFT,
            [],
            [],
        );

        // Cancelled service
        $start = (clone $now)->modify('+2 days')->setTime(16, 0);
        $this->createService(
            $org,
            'Partido Fútbol (Cancelado)',
            'Cancelado por lluvia. Se reprogramará.',
            $start,
            (clone $start)->modify('+3 hours'),
            (clone $start)->modify('-30 minutes'),
            ServiceStatus::CANCELLED,
            [$units[0]],
            [],
        );

        return $org;
    }

    /**
     * @param Speciality[] $specialities
     * @param Component[]  $components
     */
    private function createOrganization2(User $user, array $specialities, array $components): Organization
    {
        $org = new Organization();
        $org->setName('Cruz Roja Málaga');
        $org->setSlug('cruz-roja-malaga');
        $org->setDescription('Asamblea Local de Cruz Roja Española en Málaga capital');

        $membership = new OrganizationMember();
        $membership->setUser($user);
        $membership->setOrganization($org);
        $membership->setRole(OrganizationMember::ROLE_ADMIN);
        $org->addMember($membership);

        $this->em->persist($user);
        $this->em->persist($org);

        // Members
        $members = $this->createOrgMembers($org, [
            ['Javier', 'Ruiz', 'manager'],
            ['Laura', 'Moreno', 'member'],
            ['Diego', 'Jiménez', 'member'],
            ['Sara', 'Navarro', 'member'],
            ['Roberto', 'Díaz', 'manager'],
            ['Carmen', 'Muñoz', 'member'],
            ['Pablo', 'Romero', 'member'],
            ['Lucía', 'Álvarez', 'member'],
        ]);

        // Units
        $units = [];

        $units[] = $this->createUnit($org, 'SVB Alfa', 'CRE-SVB-A', $specialities['SAN'], [
            ['Conductor', 1],
            ['Socorrista', 2],
            ['Camillero', 1],
        ], $components);

        $units[] = $this->createUnit($org, 'SVB Bravo', 'CRE-SVB-B', $specialities['SAN'], [
            ['Conductor', 1],
            ['Socorrista', 2],
            ['Camillero', 1],
        ], $components);

        $units[] = $this->createUnit($org, 'Puesto Sanitario Avanzado', 'CRE-PSA-1', $specialities['SAN'], [
            ['Jefe de Equipo', 1],
            ['Socorrista', 4],
            ['Auxiliar Logístico', 2],
        ], $components);

        $units[] = $this->createUnit($org, 'Unidad de Comunicaciones', 'CRE-COM-1', $specialities['COM'], [
            ['Operador de Radio', 2],
            ['Jefe de Equipo', 1],
        ], $components);

        $units[] = $this->createUnit($org, 'Unidad de Transporte', 'CRE-TRA-1', $specialities['TRA'], [
            ['Conductor', 2],
            ['Auxiliar Logístico', 2],
        ], $components);

        $units[] = $this->createUnit($org, 'Equipo de Intervención', 'CRE-INT-1', $specialities['RES'], [
            ['Jefe de Equipo', 1],
            ['Rescatador', 4],
            ['Camillero', 2],
        ], $components);

        $now = new \DateTime('now', new \DateTimeZone('UTC'));

        // Finished services
        for ($i = 1; $i <= 4; ++$i) {
            $start = (clone $now)->modify("-{$i} weeks")->setTime(9, 0);
            $this->createService(
                $org,
                "Guardia Semanal #{$i}",
                'Turno de guardia semanal en sede. Atención a llamadas y salidas.',
                $start,
                (clone $start)->modify('+12 hours'),
                (clone $start)->modify('-15 minutes'),
                ServiceStatus::FINISHED,
                [$units[0], $units[3]],
                $members,
            );
        }

        // Confirmed upcoming
        $start = (clone $now)->modify('+2 days')->setTime(8, 0);
        $this->createService(
            $org,
            'Maratón de Málaga',
            'Dispositivo sanitario completo para el Maratón de Málaga. 8 puntos de asistencia en recorrido.',
            $start,
            (clone $start)->modify('+8 hours'),
            (clone $start)->modify('-60 minutes'),
            ServiceStatus::CONFIRMED,
            [$units[0], $units[1], $units[2], $units[3], $units[4]],
            $members,
        );

        $start = (clone $now)->modify('+4 days')->setTime(19, 0);
        $this->createService(
            $org,
            'Procesión Semana Santa',
            'Cobertura sanitaria de la procesión del Jueves Santo. Recorrido centro histórico.',
            $start,
            (clone $start)->modify('+5 hours'),
            (clone $start)->modify('-45 minutes'),
            ServiceStatus::CONFIRMED,
            [$units[0], $units[1], $units[5]],
            $members,
        );

        $start = (clone $now)->modify('+1 week')->setTime(10, 0);
        $this->createService(
            $org,
            'Torneo Rugby Playa',
            'Dispositivo en playa de La Malagueta para torneo de rugby.',
            $start,
            (clone $start)->modify('+7 hours'),
            (clone $start)->modify('-30 minutes'),
            ServiceStatus::CONFIRMED,
            [$units[0], $units[2]],
            $members,
        );

        // Draft
        $start = (clone $now)->modify('+2 weeks')->setTime(17, 0);
        $this->createService(
            $org,
            'Noche en Blanco',
            'Evento cultural nocturno. Pendiente de confirmar recursos con ayuntamiento.',
            $start,
            (clone $start)->modify('+6 hours'),
            (clone $start)->modify('-60 minutes'),
            ServiceStatus::DRAFT,
            [$units[0], $units[1]],
            [],
        );

        $start = (clone $now)->modify('+3 weeks')->setTime(9, 0);
        $this->createService(
            $org,
            'Formación Interna SVB',
            'Jornada de formación para nuevos voluntarios. Prácticas de soporte vital.',
            $start,
            (clone $start)->modify('+5 hours'),
            (clone $start)->modify('-30 minutes'),
            ServiceStatus::DRAFT,
            [],
            [],
        );

        return $org;
    }

    /**
     * @param array<array{0: string, 1: string, 2: string}> $data
     *
     * @return User[]
     */
    private function createOrgMembers(Organization $org, array $data): array
    {
        $members = [];
        foreach ($data as [$name, $surname, $role]) {
            $email = strtolower($name).'@demo.com';

            $user = new User();
            $user->setName($name);
            $user->setSurname($surname);
            $user->setEmail($email);
            $user->setDateStart(new \DateTime('now', new \DateTimeZone('UTC')));
            $user->setPassword($this->passwordHasher->hashPassword($user, 'password123'));
            $user->setRoles([]);
            $this->em->persist($user);

            $membership = new OrganizationMember();
            $membership->setUser($user);
            $membership->setOrganization($org);
            $membership->setRole($role);
            $org->addMember($membership);

            $members[] = $user;
        }

        return $members;
    }

    /**
     * @param array<array{0: string, 1: int}> $componentDefs
     * @param Component[]                     $components
     */
    private function createUnit(
        Organization $org,
        string $name,
        string $identifier,
        Speciality $speciality,
        array $componentDefs,
        array $components,
    ): Unit {
        $unit = new Unit();
        $unit->setName($name);
        $unit->setIdentifier($identifier);
        $unit->setSpeciality($speciality);
        $unit->setOrganization($org);
        $this->em->persist($unit);

        foreach ($componentDefs as [$compName, $quantity]) {
            $uc = new UnitComponent();
            $uc->setUnit($unit);
            $uc->setComponent($components[$compName]);
            $uc->setQuantity($quantity);
            $this->em->persist($uc);
            $unit->addUnitComponent($uc);
        }

        return $unit;
    }

    /**
     * @param Unit[] $units
     * @param User[] $members
     */
    private function createService(
        Organization $org,
        string $name,
        string $description,
        \DateTimeInterface $start,
        \DateTimeInterface $end,
        \DateTimeInterface $place,
        ServiceStatus $status,
        array $units,
        array $members,
    ): Service {
        $service = new Service();
        $service->setOrganization($org);
        $service->setName($name);
        $service->setDescription($description);
        $service->setDateStart($start);
        $service->setDateEnd($end);
        $service->setDatePlace($place);
        $service->setStatus($status);
        $this->em->persist($service);

        foreach ($units as $unit) {
            $service->addUnit($unit);

            // Create gaps for each unit's components
            foreach ($unit->getUnitComponents() as $uc) {
                for ($i = 0; $i < $uc->getQuantity(); ++$i) {
                    $gap = new Gap();
                    $gap->setService($service);
                    $gap->setUnitComponent($uc);

                    // Assign members to gaps for confirmed/finished services
                    if (\in_array($status, [ServiceStatus::CONFIRMED, ServiceStatus::FINISHED]) && \count($members) > 0) {
                        $gap->setUser($members[array_rand($members)]);
                    }

                    $this->em->persist($gap);
                    $service->addGap($gap);
                }
            }
        }

        return $service;
    }
}
