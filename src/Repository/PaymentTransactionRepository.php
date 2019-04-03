<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/25/19
 * Time: 1:17 PM
 */

namespace App\Repository;

use App\Entity\PaymentTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PaymentTransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentTransaction::class);
    }
}
