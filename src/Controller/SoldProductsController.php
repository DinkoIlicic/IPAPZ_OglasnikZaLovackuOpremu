<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:22 AM
 */

namespace App\Controller;

use App\Entity\PaymentTransaction;
use App\Entity\Product;
use App\Entity\Sold;
use App\Entity\User;
use App\Repository\PaymentTransactionRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class SoldProductsController extends AbstractController
{
    /**
     * @Route("/seller/handle-search-per-user/{_query?}",
     *      name="handle_search_per_user", methods={"POST", "GET"})
     * @var                                     $_query
     * @param                                   ProductService $productService
     * @return                                  JsonResponse
     */
    public function handleSearchRequestPerUser($_query, ProductService $productService)
    {
        $em = $this->getDoctrine()->getManager();
        if ($_query) {
            $data = $em->getRepository(User::class)->findByName($_query);
        } else {
            $data = $em->getRepository(User::class)->findAll();
        }

        $jsonObject = $productService->returnJsonObjectUser($data);
        return new JsonResponse($jsonObject, 200, [], true);
    }

    /**
     * @Route("/seller/handle-search-per-product/{_query?}",
     *      name="handle_search_per_product", methods={"POST", "GET"})
     * @var                                     $_query
     * @param                                   ProductService $productService
     * @return                                  JsonResponse
     */
    public function handleSearchRequestPerProduct($_query, ProductService $productService)
    {
        $em = $this->getDoctrine()->getManager();
        if ($_query) {
            $data = $em->getRepository(Product::class)->findByName($_query);
        } else {
            $data = $em->getRepository(Product::class)->findAll();
        }

        $jsonObject = $productService->returnJsonObjectUser($data);
        return new JsonResponse($jsonObject, 200, [], true);
    }

    /**
     * @Route("/admin/ajax-sold-per-user-admin/{id?}", name="ajax_sold_per_user_admin")
     * @param                                  SoldRepository $soldRepository
     * @param                                  ProductRepository $productRepository
     * @param                                  User $id
     * @return                                 JsonResponse
     */
    public function ajaxListPersonPerUserAdmin(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $hName = $id->getFullName();
            $soldPerUser = $soldRepository->getSoldProductPerUserAdmin($id, $products);
        } else {
            $hName = "All users";
            $soldPerUser = $soldRepository->getSoldProductAllAdmin($products);
        }

        return new JsonResponse(
            [
                'soldItems' => $soldPerUser,
                'hName' => $hName,
            ]
        );
    }

    /**
     * @Route("/admin/ajax-sold-per-product-admin/{id?}", name="ajax_sold_per_product_admin")
     * @param                                  SoldRepository $soldRepository
     * @param                                  ProductRepository $productRepository
     * @param                                  Product $id
     * @return                                 JsonResponse
     */
    public function ajaxListPersonPerProductAdmin(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        Product $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $hName = $id->getName();
            $soldPerUser = $soldRepository->getSoldProductPerProductAdmin($id, $products);
        } else {
            $hName = "All products";
            $soldPerUser = $soldRepository->getSoldProductAllAdmin($products);
        }

        return new JsonResponse(
            [
                'soldItems' => $soldPerUser,
                'hName' => $hName,
            ]
        );
    }

    /**
     * @Route("/seller/ajax-sold-per-user-seller/{id?}", name="ajax_sold_per_user_seller")
     * @param                                  SoldRepository $soldRepository
     * @param                                  ProductRepository $productRepository
     * @param                                  User $id
     * @return                                 JsonResponse
     */
    public function ajaxListPersonPerUserSeller(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {

        $products = $productRepository->findAll();
        if ($id) {
            $hName = $id->getFullName();
            $soldPerUser = $soldRepository->getSoldProductPerUserSeller($id, $products, $this->getUser());
        } else {
            $hName = "All users";
            $soldPerUser = $soldRepository->getSoldProductAllSeller($products, $this->getUser());
        }

        return new JsonResponse(
            [
                'soldItems' => $soldPerUser,
                'hName' => $hName,
            ]
        );
    }

    /**
     * @Route("/seller/ajax-sold-per-product-seller/{id?}", name="ajax_sold_per_product_seller")
     * @param                                  SoldRepository $soldRepository
     * @param                                  ProductRepository $productRepository
     * @param                                  Product $id
     * @return                                 JsonResponse
     */
    public function ajaxListPersonPerProductSeller(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        Product $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $hName = $id->getName();
            $soldPerUser = $soldRepository->getSoldProductPerProductSeller($id, $products, $this->getUser());
        } else {
            $hName = "All products";
            $soldPerUser = $soldRepository->getSoldProductAllSeller($products, $this->getUser());
        }

        return new JsonResponse(
            [
                'soldItems' => $soldPerUser,
                'hName' => $hName,
            ]
        );
    }

    /**
     * @Route("/admin/item-sold-per-user/{id?}", name="view_sold_items_per_user_admin")
     * @param                                    SoldRepository $soldRepository
     * @param                                    ProductRepository $productRepository
     * @param                                    User $id
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function listOfBoughtItemsPerUser(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $hName = $id->getFullName();
            $soldPerUser = $soldRepository->getSoldProductPerUserAdmin($id, $products);
        } else {
            $hName = "All users";
            $soldPerUser = $soldRepository->getSoldProductAllAdmin($products);
        };

        return $this->render(
            '/search/sold_products.html.twig',
            [
                'soldItems' => $soldPerUser,
                'hName' => $hName,
                'header'  => '/admin/header.html.twig',
                'search' => 'user',
                'searchType' => 'userAdmin',
                'searchAll' => 'view_sold_items_per_user_admin',
                'viewSold' => 'view_sold_product_info_admin',
                'viewSoldPayment' => 'view_sold_item_payment_method_per_user_admin',
                'deleteSold' => 'delete_sold_item_per_user_admin'
            ]
        );
    }

    /**
     * @Route("/admin/item-sold-per-product/{id?}", name="view_sold_items_per_product_admin")
     * @param                                 Product $id
     * @param                                 SoldRepository $soldRepository
     * @param                                 ProductRepository $productRepository
     * @return                                \Symfony\Component\HttpFoundation\Response
     */
    public function listOfBoughtItemsPerProductAdmin(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        Product $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            /**
             * @var \App\Entity\Product $product
             */
            $hName = $id->getName();
            $soldPerProduct = $soldRepository->getSoldProductPerProductAdmin($id, $products);
        } else {
            $hName = "All products";
            $soldPerProduct = $soldRepository->getSoldProductAllAdmin($products);
        }

        return $this->render(
            '/search/sold_products.html.twig',
            [
                'soldItems' => $soldPerProduct,
                'hName' => $hName,
                'header'  => '/admin/header.html.twig',
                'search' => 'product',
                'searchType' => 'productAdmin',
                'searchAll' => 'view_sold_items_per_product_admin',
                'viewSold' => 'view_sold_product_info_admin',
                'viewSoldPayment' => 'view_sold_item_payment_method_per_product_admin',
                'deleteSold' => 'delete_sold_item_per_product_admin'
            ]
        );
    }

    /**
     * @Route("/seller/item-sold-per-user/{id?}", name="view_sold_items_per_user_seller")
     * @param                                    SoldRepository $soldRepository
     * @param                                    ProductRepository $productRepository
     * @param                                    User $id
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function listOfBoughtItemsPerUserSeller(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $hName = $id->getFullName();
            $soldPerUser = $soldRepository->getSoldProductPerUserSeller($id, $products, $this->getUser());
        } else {
            $hName = "All users";
            $soldPerUser = $soldRepository->getSoldProductAllSeller($products, $this->getUser());
        };

        return $this->render(
            '/search/sold_products.html.twig',
            [
                'soldItems' => $soldPerUser,
                'hName' => $hName,
                'header'  => '/seller/header.html.twig',
                'search' => 'user',
                'searchType' => 'userSeller',
                'searchAll' => 'view_sold_items_per_user_seller',
                'viewSold' => 'view_sold_product_info_seller',
                'viewSoldPayment' => 'view_sold_item_payment_method_per_user_seller',
                'deleteSold' => 'delete_sold_item_per_user_seller'
            ]
        );
    }

    /**
     * @Route("/seller/item-sold-per-product/{id?}", name="view_sold_items_per_product_seller")
     * @param                                 Product $id
     * @param                                 SoldRepository $soldRepository
     * @param                                 ProductRepository $productRepository
     * @return                                \Symfony\Component\HttpFoundation\Response
     */
    public function listOfBoughtItemsPerProductSeller(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        Product $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            /**
             * @var \App\Entity\Product $product
             */
            $hName = $id->getName();
            $soldPerProduct = $soldRepository->getSoldProductPerProductSeller($id, $products, $this->getUser());
        } else {
            $hName = "All products";
            $soldPerProduct = $soldRepository->getSoldProductAllSeller($products, $this->getUser());
        }

        return $this->render(
            '/search/sold_products.html.twig',
            [
                'soldItems' => $soldPerProduct,
                'hName' => $hName,
                'header'  => '/seller/header.html.twig',
                'search' => 'product',
                'searchType' => 'productSeller',
                'searchAll' => 'view_sold_items_per_product_seller',
                'viewSold' => 'view_sold_product_info_seller',
                'viewSoldPayment' => 'view_sold_item_payment_method_per_product_seller',
                'deleteSold' => 'delete_sold_item_per_product_seller'
            ]
        );
    }

    private function confirmBuyAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        /**
         * @var $invoice \App\Entity\PaymentTransaction
         */
        $invoice = $paymentTransactionRepository->findOneBy(
            [
                'soldProduct' => $sold->getId()
            ]
        );
        if ($invoice->getConfirmed() === 0) {
            $invoice->setConfirmed(1);
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($invoice->getConfirmed() === 1) {
            $invoice->setConfirmed(0);
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        $entityManager->flush();
    }

    /**
     * @Route("/admin/confirm-buy-per-user-admin/{id}", name="confirm_buy_per_user_admin")
     * @param                                             EntityManagerInterface $entityManager
     * @param                                             PaymentTransactionRepository $paymentTransactionRepository
     * @param                                             Sold $sold
     * @return                                            \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerUserAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        self::confirmBuyAdmin($sold, $entityManager, $paymentTransactionRepository);
        return $this->redirectToRoute(
            'view_sold_items_per_user_admin',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/admin/confirm-buy-per-product-admin/{id}", name="confirm_buy_per_product_admin")
     * @param                                              EntityManagerInterface $entityManager
     * @param                                             PaymentTransactionRepository $paymentTransactionRepository
     * @param                                              Sold $sold
     * @return                                             \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerProductAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        self::confirmBuyAdmin($sold, $entityManager, $paymentTransactionRepository);

        return $this->redirectToRoute(
            'view_sold_items_per_product_admin',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    private function confirmBuySeller(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        /**
         * @var $invoice \App\Entity\PaymentTransaction
         */
        $invoice = $paymentTransactionRepository->findOneBy(
            [
                'user' => $sold->getUser()->getId(),
                'soldProduct' => $sold->getId()
            ]
        );
        if (!$invoice || $this->getUser() !== $sold->getProduct()->getUser()) {
            throw $this->createNotFoundException("Page not found.");
        }

        if ($invoice->getConfirmed() === 0) {
            $invoice->setConfirmed(1);
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($invoice->getConfirmed() === 1) {
            $invoice->setConfirmed(0);
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        $entityManager->flush();
    }

    /**
     * @Route("/seller/confirm-buy-per-user-seller/{id}", name="confirm_buy_per_user_seller")
     * @param                                             EntityManagerInterface $entityManager
     * @param                                             PaymentTransactionRepository $paymentTransactionRepository
     * @param                                             Sold $sold
     * @return                                            \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerUserSeller(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        self::confirmBuyAdmin($sold, $entityManager, $paymentTransactionRepository);
        return $this->redirectToRoute(
            'view_sold_items_per_user_seller',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/seller/confirm-buy-per-product-seller/{id}", name="confirm_buy_per_product_seller")
     * @param                                              EntityManagerInterface $entityManager
     * @param                                             PaymentTransactionRepository $paymentTransactionRepository
     * @param                                              Sold $sold
     * @return                                             \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerProductSeller(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        self::confirmBuySeller($sold, $entityManager, $paymentTransactionRepository);

        return $this->redirectToRoute(
            'view_sold_items_per_product_seller',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    public function deleteProductItemAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ) {
        /**
         * @var Product $productOld
         */
        $productOld = $productRepository->findOneBy(
            [
                'id' => $sold->getProduct()->getId()
            ]
        );
        if ($productOld->getAvailableQuantity() === 0) {
            $wishlistProducts = $wishlistRepository->findBy(
                [
                    'product' => $productOld->getId()]
            );
            foreach ($wishlistProducts as $wishlistProduct) {
                /**
                 * @var $wishlistProduct \App\Entity\Wishlist;
                 */
                $wishlistProduct->setNotify(1);
                $entityManager->persist($wishlistProduct);
            }
        }

        $productOld->setAvailableQuantity($productOld->getAvailableQuantity() + $sold->getQuantity());
        $entityManager->remove($sold);
        $entityManager->flush();
    }

    /**
     * @Route("/admin/delete-sold-item-per-user-admin/{id}", name="delete_sold_item_per_user_admin")
     * @param                                                  ProductRepository $productRepository
     * @param                                                  EntityManagerInterface $entityManager
     * @param                                                  WishlistRepository $wishlistRepository
     * @param                                                  Sold $sold
     * @return                                                 \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerUserAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ) {
        self::deleteProductItemAdmin($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'view_sold_items_per_user_admin',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/admin/delete-sold-item-per-product-admin/{id}", name="delete_sold_item_per_product_admin")
     * @param                                                    ProductRepository $productRepository
     * @param                                                    EntityManagerInterface $entityManager
     * @param                                                    WishlistRepository $wishlistRepository
     * @param                                                    Sold $sold
     * @return                                                   \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerProductAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ) {
        self::deleteProductItemAdmin($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'view_sold_items_per_product_admin',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    public function deleteProductItemSeller(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ) {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            throw $this->createNotFoundException("Page not found.");
        }

        /**
         * @var Product $productOld
         */
        $productOld = $productRepository->findOneBy(
            [
                'id' => $sold->getProduct()->getId()
            ]
        );
        if ($productOld->getAvailableQuantity() === 0) {
            $wishlistProducts = $wishlistRepository->findBy(
                [
                    'product' => $productOld->getId()]
            );
            foreach ($wishlistProducts as $wishlistProduct) {
                /**
                 * @var $wishlistProduct \App\Entity\Wishlist;
                 */
                $wishlistProduct->setNotify(1);
                $entityManager->persist($wishlistProduct);
            }
        }

        $productOld->setAvailableQuantity($productOld->getAvailableQuantity() + $sold->getQuantity());
        $entityManager->remove($sold);
        $entityManager->flush();
    }

    /**
     * @Route("/seller/delete-sold-item-per-user-seller/{id}", name="delete_sold_item_per_user_seller")
     * @param                                                  ProductRepository $productRepository
     * @param                                                  EntityManagerInterface $entityManager
     * @param                                                  WishlistRepository $wishlistRepository
     * @param                                                  Sold $sold
     * @return                                                 \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerUserSeller(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ) {
        self::deleteProductItemSeller($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'view_sold_items_per_user_seller',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/seller/delete-sold-item-per-product-seller/{id}", name="delete_sold_item_per_product_seller")
     * @param                                                    ProductRepository $productRepository
     * @param                                                    EntityManagerInterface $entityManager
     * @param                                                    WishlistRepository $wishlistRepository
     * @param                                                    Sold $sold
     * @return                                                   \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerProductSeller(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository
    ) {
        self::deleteProductItemSeller($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'view_sold_items_per_product_seller',
            [
                'id' => $sold->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/admin/sold-product/{id}", name="view_sold_product_info_admin")
     * @param                             Sold $sold
     * @return                            \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldProductInfoAdmin(Sold $sold)
    {
        return $this->render(
            '/admin/view_sold_item.html.twig',
            [
                'sold' => $sold
            ]
        );
    }

    /**
     * @Route("/seller/sold-product/{id}", name="view_sold_product_info_seller")
     * @param                             Sold $sold
     * @return                            \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldProductInfoSeller(Sold $sold)
    {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            throw $this->createNotFoundException("Page not found.");
        }

        return $this->render(
            '/seller/view_sold_item.html.twig',
            [
                'sold' => $sold
            ]
        );
    }

    /**
     * @Route("/admin/sold-item-payment-method-per-user/{id?}", name="view_sold_item_payment_method_per_user_admin")
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldItemPerUserPaymentMethodAdmin(
        PaymentTransaction $paymentTransaction
    ) {
        return $this->render(
            '/admin/view_sold_item_payment_method.html.twig',
            [
                'payment' => $paymentTransaction,
                'deletePayment' => 'delete_payment_transaction_per_user_admin',
                'confirmPayment' => 'confirm_payment_transaction_per_user_admin'
            ]
        );
    }

    /**
     * @Route("/admin/sold-item-payment-method-per-product/{id?}", name="view_sold_item_payment_method_per_product_admin")
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldItemPerProductPaymentMethodAdmin(
        PaymentTransaction $paymentTransaction
    ) {
        return $this->render(
            '/admin/view_sold_item_payment_method.html.twig',
            [
                'payment' => $paymentTransaction,
                'deletePayment' => 'delete_payment_transaction_per_product_admin',
                'confirmPayment' => 'confirm_payment_transaction_per_product_admin'
            ]
        );
    }

    /**
     * @Route("/seller/sold-item-payment-per-user-method/{id?}", name="view_sold_item_payment_method_per_user_seller")
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldItemPerUserPaymentMethodSeller(
        PaymentTransaction $paymentTransaction
    ) {
        return $this->render(
            '/seller/view_sold_item_payment_method.html.twig',
            [
                'payment' => $paymentTransaction,
                'deletePayment' => 'delete_payment_transaction_per_user_seller',
                'confirmPayment' => 'confirm_payment_transaction_per_user_seller'
            ]
        );
    }

    /**
     * @Route("/seller/sold-item-payment-per-product-method/{id?}", name="view_sold_item_payment_method_per_product_seller")
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldItemPerProductPaymentMethodSeller(
        PaymentTransaction $paymentTransaction
    ) {
        return $this->render(
            '/seller/view_sold_item_payment_method.html.twig',
            [
                'payment' => $paymentTransaction,
                'deletePayment' => 'delete_payment_transaction_per_product_seller',
                'confirmPayment' => 'confirm_payment_transaction_per_product_seller'
            ]
        );
    }
}
