<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/19/19
 * Time: 1:27 PM
 */

namespace App\Repository;

use App\Entity\CustomPage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CustomPageRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, CustomPage::class);
    }
}
