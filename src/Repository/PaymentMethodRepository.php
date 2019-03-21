<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/21/19
 * Time: 2:17 PM
 */

namespace App\Repository;

use App\Entity\PaymentMethod;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class PaymentMethodRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, PaymentMethod::class);
    }
}
