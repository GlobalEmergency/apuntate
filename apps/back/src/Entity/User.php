<?php

namespace GlobalEmergency\Apuntate\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use GlobalEmergency\Apuntate\Entity\Traits\Timestampable;
use GlobalEmergency\Apuntate\Repository\UserRepository;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]
#[ORM\HasLifecycleCallbacks]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use Timestampable;

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $surname;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $email;

    #[ORM\Column(type: 'string', length: 255)]
    private string $password;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTimeInterface $dateStart = null;

    #[ORM\Column(type: 'date', nullable: true)]
    private ?\DateTime $dateEnd = null;

    /** @var array<string> */
    #[ORM\Column(type: 'array')]
    private array $roles = [];

    /** @var Collection<int, Requirement> */
    #[ORM\ManyToMany(targetEntity: Requirement::class, inversedBy: 'users')]
    private Collection $requirements;

    /** @var Collection<int, Gap> */
    #[ORM\OneToMany(targetEntity: Gap::class, mappedBy: 'user')]
    private Collection $gaps;

    /** @var Collection<int, OrganizationMember> */
    #[ORM\OneToMany(targetEntity: OrganizationMember::class, mappedBy: 'user', cascade: ['persist'])]
    private Collection $memberships;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->requirements = new ArrayCollection();
        $this->gaps = new ArrayCollection();
        $this->memberships = new ArrayCollection();
    }

    public function getId(): ?Uuid
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSurname(): ?string
    {
        return $this->surname;
    }

    public function setSurname(string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        if (!is_null($password)) {
            $this->password = $password;
        }

        return $this;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTime
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTime $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /** @param array<string> $roles */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function __toString(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
    }

    /** @return Collection<int, Requirement> */
    public function getRequirements(): Collection
    {
        return $this->requirements;
    }

    public function addRequirement(Requirement $requirement): self
    {
        if (!$this->requirements->contains($requirement)) {
            $this->requirements[] = $requirement;
        }

        return $this;
    }

    public function removeRequirement(Requirement $requirement): self
    {
        $this->requirements->removeElement($requirement);

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
            $gap->setUser($this);
        }

        return $this;
    }

    public function removeGap(Gap $gap): self
    {
        if ($this->gaps->removeElement($gap)) {
            // set the owning side to null (unless already changed)
            if ($gap->getUser() === $this) {
                $gap->setUser(null);
            }
        }

        return $this;
    }

    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    /** @return Collection<int, OrganizationMember> */
    public function getMemberships(): Collection
    {
        return $this->memberships;
    }

    /** @return Organization[] */
    public function getOrganizations(): array
    {
        return $this->memberships->map(fn (OrganizationMember $m) => $m->getOrganization())->toArray();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->setDateStart(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->setCreatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
        $this->setUpdatedAt(new \DateTime('now', new \DateTimeZone('UTC')));
    }
}
