<?php

namespace App\Repository;

use App\Entity\OrdersDetails;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OrdersDetails>
 *
 * @method OrdersDetails|null find($id, $lockMode = null, $lockVersion = null)
 * @method OrdersDetails|null findOneBy(array $criteria, array $orderBy = null)
 * @method OrdersDetails[]    findAll()
 * @method OrdersDetails[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OrdersDetailsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OrdersDetails::class);
    }

//    /**
//     * @return OrdersDetails[] Returns an array of OrdersDetails objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('o.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?OrdersDetails
//    {
//        return $this->createQueryBuilder('o')
//            ->andWhere('o.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }

    public function findByOrderId($id)
    {
        return $this->createQueryBuilder('od')
            ->where('od.orders = :id')
            ->setParameter('order_id', $id)
            ->getQuery()
            ->getResult();
    }

    // public function getOrdersDetails($id)
    // {
    //     return $this->createQueryBuilder('od')
    //         ->select('od')
    //         ->from(orders::class, 'o')
    //         ->where('o.orders_id = :id')
    // }

    // public function yourAction()
    // {
    //     $sql = "SELECT * FROM users WHERE age > :age";
    //     $query = $this->entityManager->createNativeQuery($sql, new ResultSetMapping());
    //     $query->setParameter('age', 18);
    //     $users = $query->getResult();

    //     // Faire quelque chose avec les résultats de la requête
    // }


}
