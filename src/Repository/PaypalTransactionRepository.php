<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/22/19
 * Time: 9:35 AM
 */

namespace App\Repository;

use App\Entity\PaypalTransaction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PaypalTransactionRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaypalTransaction::class);
    }
}
