<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/22/19
 * Time: 12:48 PM
 */

namespace App\Repository;

use App\Entity\OnDeliveryTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class OnDeliveryTransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, OnDeliveryTransaction::class);
    }
}
