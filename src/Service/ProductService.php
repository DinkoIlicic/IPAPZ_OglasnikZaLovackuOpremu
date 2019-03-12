<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 25.02.19.
 * Time: 11:33
 */

namespace App\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Product;
use App\Entity\Sold;

class ProductService
{
    protected $em;
    protected $container;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerInterface $container
    )
    {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function returnData($request)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
            SELECT 
              p
            FROM 
              App\Entity\Product p
            JOIN 
              p.categories c
            WHERE 
              p.visibility = 1 and
              p.visibilityAdmin = 1 and
              c.visibilityAdmin = 1
            ORDER BY
              p.id DESC
            '
        );
        //$result = $query->execute();
        $pagenator = $container->get('knp_paginator');
        $results = $pagenator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 9)
        );
        return $results;
    }

    public function returnDataPerCategory($request, $category)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
            SELECT 
              p
            FROM 
              App\Entity\Product p
            JOIN
              p.category c
            WHERE 
              p.visibility = 1 and
              p.visibilityAdmin = 1 and
              p.category = :category and
              c.visibilityAdmin = 1
            ORDER BY
              p.id DESC
            '
        );
        $query->setParameter('category', $category);
        //$result = $query->execute();
        $pagenator = $container->get('knp_paginator');
        $results = $pagenator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 9)
        );
        return $results;
    }

    public function returnDataMyItems($request, $user)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
            SELECT 
              p
            FROM 
              App\Entity\Sold p
            WHERE 
              p.user = :user
            ORDER BY
              p.boughtAt DESC
            '
        );
        $query->setParameter('user', $user);
        //$result = $query->execute();
        $pagenator = $container->get('knp_paginator');
        $results = $pagenator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 9)
        );
        return $results;
    }
}