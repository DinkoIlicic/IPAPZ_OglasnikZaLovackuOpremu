<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:39 AM
 */

namespace App\Repository;

use App\Entity\Shipping;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class ShippingRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Shipping::class);
    }

    /**
     * @return Shipping[] Returns an array of Shipping objects
     * @param  $value
     */
    public function findByName($value)
    {
        return $this->createQueryBuilder('s')
            ->select('s.country')
            ->andWhere('s.country like :query')
            ->setParameter('query', "%" . $value . "%")
            ->orderBy('s.country', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findCountry($value)
    {
        return $this->createQueryBuilder('s')
            ->select('s.country', 's.price')
            ->andWhere('s.country = :query')
            ->setParameter('query', $value)
            ->orderBy('s.country', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }
}
