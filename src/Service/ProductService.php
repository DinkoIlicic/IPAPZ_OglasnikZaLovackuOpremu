<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 25.02.19.
 * Time: 11:33
 */

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProductService
{
    protected $em;
    protected $container;

    public function __construct(
        EntityManagerInterface $entityManager,
        ContainerInterface $container
    ) {
        $this->em = $entityManager;
        $this->container = $container;
    }

    public function returnAllProducts($request)
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
              p.productCategory c
            JOIN
              c.category d
            WHERE 
              p.visibility = 1 and
              p.visibilityAdmin = 1 and
              d.visibilityAdmin = 1
            ORDER BY
              p.id DESC
            '
        );
        $paginator = $container->get('knp_paginator');
        $results = $paginator->paginate(
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
            INNER JOIN
              p.productCategory c
            JOIN
              c.category d
            WHERE 
              d.id = :category and
              p.visibility = 1 and
              p.visibilityAdmin = 1 and
              d.visibilityAdmin = 1
            ORDER BY
              p.id DESC
            '
        );
        $query->setParameter('category', $category);
        $paginator = $container->get('knp_paginator');
        $results = $paginator->paginate(
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
        $paginator = $container->get('knp_paginator');
        $results = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 9)
        );
        return $results;
    }

    public function returnDataMyWishlist($request, $user)
    {
        $em = $this->em;
        $container = $this->container;
        $query = $em->createQuery(
            '
            SELECT 
              p
            FROM 
              App\Entity\Wishlist p
            WHERE 
              p.user = :user
            '
        );
        $query->setParameter('user', $user);
        $paginator = $container->get('knp_paginator');
        $results = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $request->query->getInt('limit', 9)
        );
        return $results;
    }
}
