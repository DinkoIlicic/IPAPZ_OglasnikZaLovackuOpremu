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
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Repository\PaymentTransactionRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class SoldProductsController extends AbstractController
{
    /**
     * @Route("/admin/handle-search-per-user-admin/{_query?}",
     *      name="handle_search_per_user_admin", methods={"POST", "GET"})
     * @var                                     $_query
     * @param                                   ProductService $productService
     * @return                                  JsonResponse
     */
    public function handleSearchRequestPerUserAdmin($_query, ProductService $productService)
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
     * @Route("/admin/ajax-person-sold-per-user-admin/{id?}", name="ajax_person_sold_admin_user")
     * @param                                  SoldRepository $soldRepository
     * @param                                  ProductRepository $productRepository
     * @param                                  User $id
     * @return                                 JsonResponse
     */
    public function ajaxListPersonPerPersonAdmin(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $userName = $id->getFullName();
            $soldPerUser = $soldRepository->getSoldProductPerUser($id, $products);
        } else {
            $userName = "All users";
            $soldPerUser = $soldRepository->getSoldProductPerUserAll($products);
        }

        return new JsonResponse(
            [
                'soldItems' => $soldPerUser,
                'userName' => $userName,
            ]
        );
    }

    /**
     * @Route("/admin/item-sold-per-user/{id?}", name="view_sold_items_per_person")
     * @param                                    SoldRepository $soldRepository
     * @param                                    ProductRepository $productRepository
     * @param                                    User $id
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function listOfPeopleThatBoughtMyProduct(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {
        $products = $productRepository->findAll();
        if ($id) {
            $userName = $id->getFullName();
            $soldPerUser = $soldRepository->getSoldProductPerUser($id, $products);
        } else {
            $userName = "All users";
            $soldPerUser = $soldRepository->getSoldProductPerUserAll($products);
        };

        return $this->render(
            '/admin/view_sold_items_per_person.html.twig',
            [
                'soldItems' => $soldPerUser,
                'userName' => $userName,
            ]
        );
    }

    /**
     * @Route("/admin/sold-item-payment-method/{id?}", name="view_sold_items_per_user_payment_method")
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldItemPerUserPaymentMethod(
        PaymentTransaction $paymentTransaction
    ) {
        return $this->render(
            '/admin/view_sold_item_payment_method.html.twig',
            [
                'payment' => $paymentTransaction
            ]
        );
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
        /**
         * @var $invoice \App\Entity\PaymentTransaction
         */
        $invoice = $paymentTransactionRepository->findOneBy(
            [
                'user' => $sold->getUser()->getId(),
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
        return $this->redirectToRoute(
            'view_sold_items_per_person',
            [
                'id' => $sold->getUser()->getId()]
        );
    }

    /**
     * @Route("/admin/delete-sold-item-per-user-admin/{id}", name="delete_sold_item_per_user_admin")
     * @param                                                  ProductRepository $productRepository
     * @param                                                  EntityManagerInterface $entityManager
     * @param                                                  WishlistRepository $wishlistRepository
     * @param                                                  ProductService $productService
     * @param                                                  Sold $sold
     * @return                                                 \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerUser(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        ProductService $productService
    ) {
        $productService->deleteProductItem($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'view_sold_items_per_person',
            [
                'id' => $sold->getUser()->getId()]
        );
    }

    /**
     * @Route("/admin/item-sold-per-product", name="view_sold_items_per_product")
     * @param                                 Request $request
     * @param                                 SoldRepository $soldRepository
     * @param                                 ProductRepository $productRepository
     * @return                                \Symfony\Component\HttpFoundation\Response
     */
    public function listOfBoughtItemsPerProduct(
        Request $request,
        SoldRepository $soldRepository,
        ProductRepository $productRepository
    ) {
        $products = $productRepository->findAll();
        $form = $this->createForm(AdminListOfBoughtItemsPerProductFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData()->getProduct();
            $message = $product->getName();
            $listOfSoldItems = $soldRepository->findBy(
                [
                    'product' => $product->getId()
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        } else {
            $message = "All products";
            $listOfSoldItems = $soldRepository->findBy(
                [
                    'product' => $products
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        }

        return $this->render(
            '/admin/view_sold_items_per_product.html.twig',
            [
                'form' => $form->createView(),
                'soldItems' => $listOfSoldItems,
                'message' => $message
            ]
        );
    }

    /**
     * @Route("/admin/confirm-buy-per-product-admin/{id}", name="confirm_buy_per_product_admin")
     * @param                                              EntityManagerInterface $entityManager
     * @param                                              Sold $sold
     * @return                                             \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerProductAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager
    ) {
        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        $entityManager->flush();
        return $this->redirectToRoute('view_sold_items_per_product');
    }

    /**
     * @Route("/seller/delete-sold-item-per-product-admin/{id}", name="delete_sold_item_per_product_admin")
     * @param                                                    ProductRepository $productRepository
     * @param                                                    EntityManagerInterface $entityManager
     * @param                                                    WishlistRepository $wishlistRepository
     * @param                                                    ProductService $productService
     * @param                                                    Sold $sold
     * @return                                                   \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        ProductService $productService
    ) {
        $productService->deleteProductItem($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute('view_sold_items_per_product');
    }

    /**
     * @Route("/admin/sold-product/{id}", name="view_sold_product_info_admin")
     * @param                             Sold $sold
     * @return                            \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldProductInfo(Sold $sold)
    {
        return $this->render(
            '/admin/view_sold_item.html.twig',
            [
                'sold' => $sold
            ]
        );
    }
}
