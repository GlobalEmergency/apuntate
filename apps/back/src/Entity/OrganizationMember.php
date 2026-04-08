<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Entity;

use Doctrine\ORM\Mapping as ORM;
use GlobalEmergency\Apuntate\Entity\Traits\Timestampable;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'organization_members')]
#[ORM\UniqueConstraint(name: 'unique_org_user', columns: ['organization_id', 'user_id'])]
#[ORM\HasLifecycleCallbacks]
class OrganizationMember
{
    use Timestampable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MANAGER = 'manager';
    public const ROLE_MEMBER = 'member';

    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    private Uuid $id;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'members')]
    #[ORM\JoinColumn(nullable: false)]
    private Organization $organization;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'memberships')]
    #[ORM\JoinColumn(nullable: false)]
    private User $user;

    #[ORM\Column(type: 'string', length: 20)]
    private string $role = self::ROLE_MEMBER;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getOrganization(): Organization
    {
        return $this->organization;
    }

    public function setOrganization(Organization $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): self
    {
        if (!\in_array($role, [self::ROLE_ADMIN, self::ROLE_MANAGER, self::ROLE_MEMBER], true)) {
            throw new \InvalidArgumentException(sprintf('Invalid organization role: %s', $role));
        }
        $this->role = $role;

        return $this;
    }

    public function isAdmin(): bool
    {
        return self::ROLE_ADMIN === $this->role;
    }

    public function isManager(): bool
    {
        return \in_array($this->role, [self::ROLE_ADMIN, self::ROLE_MANAGER], true);
    }
}
