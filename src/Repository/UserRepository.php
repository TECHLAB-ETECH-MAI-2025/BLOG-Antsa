<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function countUsersByRole(string $role): int
    {
    $connection = $this->getEntityManager()->getConnection();

    $sql = 'SELECT COUNT(*) FROM "user" WHERE roles::text LIKE :role';
    $stmt = $connection->prepare($sql);
    $stmt->bindValue('role', '%"'.$role.'"%');
    $result = $stmt->executeQuery();

    return (int) $result->fetchOne();
    }
    public function countUsersByIsVerified(bool $isVerified): int
    {
    return (int) $this->createQueryBuilder('u')
        ->select('COUNT(u.id)')
        ->where('u.isVerified = :isVerified')
        ->setParameter('isVerified', $isVerified)
        ->getQuery()
        ->getSingleScalarResult();
    }

    public function findAllUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('JSON_CONTAINS(u.roles, :roleAdmin) = 1 OR JSON_CONTAINS(u.roles, :roleSuperAdmin) = 1')
            ->setParameter('roleAdmin', json_encode('ROLE_ADMIN'))
            ->setParameter('roleSuperAdmin', json_encode('ROLE_SUPER_ADMIN'))
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    
    public function findVerifiedUsers(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.isVerified = :verified')
            ->setParameter('verified', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

