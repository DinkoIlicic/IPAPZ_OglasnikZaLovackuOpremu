<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/27/19
 * Time: 12:40 PM
 */

namespace App\Repository;

use App\Entity\UserAddress;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class UserAddressRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, UserAddress::class);
    }
}
