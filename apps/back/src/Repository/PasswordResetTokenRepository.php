<?php

declare(strict_types=1);

namespace GlobalEmergency\Apuntate\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use GlobalEmergency\Apuntate\Entity\PasswordResetToken;
use GlobalEmergency\Apuntate\Entity\User;

/** @extends ServiceEntityRepository<PasswordResetToken> */
class PasswordResetTokenRepository extends ServiceEntityRepository implements PasswordResetTokenRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PasswordResetToken::class);
    }

    public function save(PasswordResetToken $token): void
    {
        $this->getEntityManager()->persist($token);
        $this->getEntityManager()->flush();
    }

    public function findByHashedToken(string $hashedToken): ?PasswordResetToken
    {
        return $this->findOneBy(['token' => $hashedToken]);
    }

    public function invalidateExistingTokensForUser(User $user): void
    {
        $this->createQueryBuilder('t')
            ->update()
            ->set('t.used', ':used')
            ->where('t.user = :user')
            ->andWhere('t.used = false')
            ->setParameter('used', true)
            ->setParameter('user', $user)
            ->getQuery()
            ->execute();
    }
}
