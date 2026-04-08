<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use GlobalEmergency\Apuntate\Entity\Traits\Timestampable;
use GlobalEmergency\Apuntate\Repository\OrganizationRepository;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ORM\Table(name: 'organizations')]
#[ORM\HasLifecycleCallbacks]
class Organization
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private string $slug;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\OneToMany(targetEntity: OrganizationMember::class, mappedBy: 'organization', cascade: ['persist'], orphanRemoval: true)]
    private Collection $members;

    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'organization')]
    private Collection $services;

    #[ORM\OneToMany(targetEntity: Unit::class, mappedBy: 'organization')]
    private Collection $units;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->members = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->units = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = strtolower(trim(preg_replace('/[^a-zA-Z0-9]+/', '-', $slug), '-'));

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(OrganizationMember $member): self
    {
        if (!$this->members->contains($member)) {
            $this->members->add($member);
            $member->setOrganization($this);
        }

        return $this;
    }

    public function getServices(): Collection
    {
        return $this->services;
    }

    public function getUnits(): Collection
    {
        return $this->units;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}
