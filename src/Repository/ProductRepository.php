<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 20.02.19.
 * Time: 13:24
 */

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function getProductsFromCategory($category)
    {
        return $this->createQueryBuilder('p')
            ->innerJoin('p.productCategory', 'c')
            ->andWhere('c.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getResult();
    }

    public function findOnlyIds()
    {
        return $this->createQueryBuilder('p')
            ->select('p.id')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Product[] Returns an array of User objects
     * @param  $value
     */
    public function findByName($value)
    {
        return $this->createQueryBuilder('p')
            ->select('p.id', '(p.name) as fullName')
            ->andWhere('p.name like :query')
            ->setParameter('query', "%" . $value . "%")
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
