<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 25.02.19.
 * Time: 11:33
 */

namespace App\Service;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

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

    public function returnDataPerCategory($request, $urlName)
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
              d.urlName = :urlName and
              p.visibility = 1 and
              p.visibilityAdmin = 1 and
              d.visibilityAdmin = 1
            ORDER BY
              p.id DESC
            '
        );
        $query->setParameter('urlName', $urlName);
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

    /**
     * @param $customUrl
     * @param Product $product
     * @return string
     */
    public function createCustomUrl($customUrl, Product $product)
    {
        if (empty($customUrl)) {
            $customUrl = $product->getName();
        }

        $productUrlNum = '-' . rand(10000000, 99999999);
        $pageName = $customUrl . $productUrlNum;
        return $pageName;
    }

    /**
     * @param $customUrl
     * @param Product $product
     * @param $productUrlNum
     * @return string
     */
    public function changeCustomUrl($customUrl, Product $product, $productUrlNum)
    {
        if (empty($customUrl)) {
            $customUrl = $product->getName();
            $productUrlNum = '-' . rand(10000000, 99999999);
        }

        $pageName = $customUrl . $productUrlNum;
        return $pageName;
    }

    /**
     * @var                                     $data
     * @return                                  \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function returnJsonObjectUser($data)
    {
        // setting up the serializer
        $normalizers = [
            new ObjectNormalizer()
        ];
        $encoders = [
            new JsonEncoder()
        ];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonObject = $serializer->serialize(
            $data,
            'json',
            [
                'circular_reference_handler' => function ($user) {
                    /**
                     * @var $user \App\Entity\User
                     */
                    return $user->getId();
                }
            ]
        );
        return $jsonObject;
    }

    public function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
