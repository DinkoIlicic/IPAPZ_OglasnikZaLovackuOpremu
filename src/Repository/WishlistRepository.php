<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/14/19
 * Time: 1:55 PM
 */

namespace App\Repository;

use App\Entity\Wishlist;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class WishlistRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Wishlist::class);
    }
}
