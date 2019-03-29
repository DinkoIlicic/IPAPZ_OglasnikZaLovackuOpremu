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
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                'pr.name',
                'u.fullName',
                'se.fullName'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->setMaxResults(1)
            ->getQuery()
            ->getResult();
    }

    public function getSoldProductPerUserAdmin($id, $products)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                '(pr.name) as productName',
                '(u.fullName) as buyerName',
                '(se.fullName) as sellerName',
                '(pt.id) as ptId',
                '(pt.method) as ptMethod',
                '(pt.transactionId) as ptTId',
                '(pt.confirmed) as ptConfirmed'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->leftJoin('\App\Entity\PaymentTransaction', 'pt', 'WITH', 'pt.soldProduct = s.id')
            ->andWhere('s.product in (:products)', 's.user = :id')
            ->setParameter('products', $products)
            ->setParameter('id', $id)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSoldProductPerUserSeller($id, $products, $seller)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                '(pr.name) as productName',
                '(u.fullName) as buyerName',
                '(se.fullName) as sellerName',
                '(pt.id) as ptId',
                '(pt.method) as ptMethod',
                '(pt.transactionId) as ptTId',
                '(pt.confirmed) as ptConfirmed'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->leftJoin('\App\Entity\PaymentTransaction', 'pt', 'WITH', 'pt.soldProduct = s.id')
            ->andWhere('s.product in (:products)', 's.user = :id', 'pr.user = :seller')
            ->setParameter('products', $products)
            ->setParameter('id', $id)
            ->setParameter('seller', $seller)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSoldProductAllAdmin($products)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                '(pr.name) as productName',
                '(u.fullName) as buyerName',
                '(se.fullName) as sellerName',
                '(pt.id) as ptId',
                '(pt.method) as ptMethod',
                '(pt.transactionId) as ptTId',
                '(pt.confirmed) as ptConfirmed'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->leftJoin('\App\Entity\PaymentTransaction', 'pt', 'WITH', 'pt.soldProduct = s.id')
            ->andWhere('s.product in (:products)')
            ->setParameter('products', $products)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSoldProductAllSeller($products, $seller)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                '(pr.name) as productName',
                '(u.fullName) as buyerName',
                '(se.fullName) as sellerName',
                '(pt.id) as ptId',
                '(pt.method) as ptMethod',
                '(pt.transactionId) as ptTId',
                '(pt.confirmed) as ptConfirmed'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->leftJoin('\App\Entity\PaymentTransaction', 'pt', 'WITH', 'pt.soldProduct = s.id')
            ->andWhere('s.product in (:products)', 'pr.user = :seller')
            ->setParameter('products', $products)
            ->setParameter('seller', $seller)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSoldProductPerProductAdmin($id, $products)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                '(pr.name) as productName',
                '(u.fullName) as buyerName',
                '(se.fullName) as sellerName',
                '(pt.id) as ptId',
                '(pt.method) as ptMethod',
                '(pt.transactionId) as ptTId',
                '(pt.confirmed) as ptConfirmed'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->leftJoin('\App\Entity\PaymentTransaction', 'pt', 'WITH', 'pt.soldProduct = s.id')
            ->andWhere('s.product in (:products)', 's.product = :id')
            ->setParameter('products', $products)
            ->setParameter('id', $id)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function getSoldProductPerProductSeller($id, $products, $seller)
    {
        return $this->createQueryBuilder('s')
            ->select(
                's.id',
                's.quantity',
                's.price',
                's.totalPrice',
                's.confirmed',
                's.boughtAt',
                '(pr.name) as productName',
                '(u.fullName) as buyerName',
                '(se.fullName) as sellerName',
                '(pt.id) as ptId',
                '(pt.method) as ptMethod',
                '(pt.transactionId) as ptTId',
                '(pt.confirmed) as ptConfirmed'
            )
            ->innerJoin('s.product', 'pr')
            ->innerJoin('pr.user', 'se')
            ->innerJoin('s.user', 'u')
            ->leftJoin('\App\Entity\PaymentTransaction', 'pt', 'WITH', 'pt.soldProduct = s.id')
            ->andWhere('s.product in (:products)', 's.product = :id', 'pr.user = :seller')
            ->setParameter('products', $products)
            ->setParameter('id', $id)
            ->setParameter('seller', $seller)
            ->orderBy('s.boughtAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
