<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 21.02.19.
 * Time: 08:16
 */

namespace App\Repository;

use App\Entity\Sold;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class SoldRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Sold::class);
    }

    public function getOneSoldProduct()
    {
        return $this->createQueryBuilder('s')
            ->select('s.id', 's.quantity', 's.price', 's.totalPrice', 's.confirmed', 's.boughtAt', 'pr.name', 'u.fullName', 'se.fullName')
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()
            ;
    }

    public function getSoldProductPerUser($id, $products)
    {
        return $this->createQueryBuilder('s')
            ->select('s.id', 's.quantity', 's.price', 's.totalPrice', 's.confirmed', 's.boughtAt', 'pr.name', '(u.fullName) as buyerName', '(se.fullName) as sellerName')
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->andWhere('s.product in (:products)', 's.user = :id')
            ->setParameter('products', $products)
            ->setParameter('id', $id)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult()
            ;
    }
}