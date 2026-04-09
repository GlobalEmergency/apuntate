<?php

namespace GlobalEmergency\Apuntate\Entity;

use Carbon\Carbon;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use GlobalEmergency\Apuntate\Entity\Traits\Timestampable;
use GlobalEmergency\Apuntate\Repository\ServiceRepository;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Service
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'carbon')]
    private \DateTimeInterface $dateStart;

    #[ORM\Column(type: 'carbon')]
    private \DateTimeInterface $dateEnd;

    #[ORM\Column(type: 'carbon')]
    private \DateTimeInterface $datePlace;

    /** @var Collection<int, Unit> */
    #[ORM\ManyToMany(targetEntity: Unit::class, inversedBy: 'services')]
    #[MaxDepth(1)]
    private Collection $units;

    /** @var Collection<int, Gap> */
    #[ORM\OneToMany(targetEntity: Gap::class, mappedBy: 'service', cascade: ['persist'])]
    #[MaxDepth(1)]
    private Collection $gaps;

    #[ORM\Column(type: 'string')]
    private string $status;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: true)]
    private ?Organization $organization = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->units = new ArrayCollection();
        $this->gaps = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): self
    {
        $this->id = $id;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDateStart(): Carbon
    {
        if (!$this->dateStart instanceof Carbon) {
            $this->dateStart = Carbon::instance($this->dateStart);
        }

        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart instanceof Carbon ? $dateStart : Carbon::instance($dateStart);

        return $this;
    }

    /** @return Collection<int, Unit> */
    public function getUnits(): Collection
    {
        return $this->units;
    }

    public function addUnit(Unit $unit): self
    {
        if (!$this->units->contains($unit)) {
            $this->units[] = $unit;
        }

        return $this;
    }

    public function removeUnit(Unit $unit): self
    {
        $this->units->removeElement($unit);

        return $this;
    }

    /** @return Collection<int, Gap> */
    public function getGaps(): Collection
    {
        return $this->gaps;
    }

    public function addGap(Gap $gap): self
    {
        if (!$this->gaps->contains($gap)) {
            $this->gaps[] = $gap;
            $gap->setService($this);
        }

        return $this;
    }

    public function removeGap(Gap $gap): self
    {
        if ($this->gaps->removeElement($gap)) {
            if ($gap->getService() === $this) {
                $gap->setService(null);
            }
        }

        return $this;
    }

    public function getDateEnd(): Carbon
    {
        if (!$this->dateEnd instanceof Carbon) {
            $this->dateEnd = Carbon::instance($this->dateEnd);
        }

        return $this->dateEnd;
    }

    public function setDateEnd(\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd instanceof Carbon ? $dateEnd : Carbon::instance($dateEnd);

        return $this;
    }

    public function getDatePlace(): Carbon
    {
        if (!$this->datePlace instanceof Carbon) {
            $this->datePlace = Carbon::instance($this->datePlace);
        }

        return $this->datePlace;
    }

    public function setDatePlace(\DateTimeInterface $datePlace): self
    {
        $this->datePlace = $datePlace instanceof Carbon ? $datePlace : Carbon::instance($datePlace);

        return $this;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getStatus(): ServiceStatus
    {
        return ServiceStatus::from($this->status);
    }

    public function setStatus(ServiceStatus $status): self
    {
        $this->status = $status->value;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }
}
