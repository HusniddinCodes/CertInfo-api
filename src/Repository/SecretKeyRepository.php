<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\SecretKey;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<SecretKey>
 *
 * @method SecretKey|null find($id, $lockMode = null, $lockVersion = null)
 * @method SecretKey|null findOneBy(array $criteria, array $orderBy = null)
 * @method SecretKey[]    findAll()
 * @method SecretKey[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */

class SecretKeyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SecretKey::class);
    }

    public function save(SecretKey $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(SecretKey $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneToken(string $token): ?SecretKey
    {
        return $this->createQueryBuilder('rt')
            ->andWhere('rt.token = :token')
            ->setParameter('token', $token)
            ->getQuery()
            ->getOneOrNullResult();
    }

//    /**
//     * @return SecretKey[] Returns an array of SecretKey objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('r.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?SecretKey
//    {
//        return $this->createQueryBuilder('r')
//            ->andWhere('r.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
