<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Sold;
use App\Entity\User;
use App\Form\ListOfBoughtItemsPerProductFormType;
use App\Form\ProductFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductQuantityFormType;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class SellerController extends AbstractController
{
    /**
     * @Route("/seller/", name="seller_index")
     * @return            \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render(
            'seller/index.html.twig'
        );
    }

    /**
     * @Route("/seller/new-product", name="insert_product")
     * @param                        Request $request
     * @param                        EntityManagerInterface $entityManager
     * @param                        ProductService $productService
     * @return                       \Symfony\Component\HttpFoundation\Response
     */
    public function newProduct(Request $request, EntityManagerInterface $entityManager, ProductService $productService)
    {
        $form = $this->createForm(ProductFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $file = $product->getImage();
            $fileName = $productService->generateUniqueFileName() . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $customUrl = $product->getCustomUrl();
            $pageName = $productService->createCustomUrl($customUrl, $product);
            $product->setCustomUrl(str_replace(' ', '-', $pageName));
            $product->setUser($this->getUser());
            $product->setVisibility(1);
            $product->setVisibilityAdmin(1);
            $product->setImage($fileName);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Inserted new product!');
            return $this->redirectToRoute('insert_product');
        }

        return $this->render(
            '/seller/new_product.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/seller/all-products", name="show_products")
     * @param                         ProductRepository $productRepository
     * @return                        \Symfony\Component\HttpFoundation\Response
     */
    public function showAllProducts(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy(
            [],
            [
                'name' => 'ASC'
            ]
        );
        return $this->render(
            '/seller/show_all_products.html.twig',
            [
                'products' => $products
            ]
        );
    }

    /**
     * @Route("/seller/my-products", name="show_my_products")
     * @param                        ProductRepository $productRepository
     * @return                       \Symfony\Component\HttpFoundation\Response
     */
    public function showMyProducts(ProductRepository $productRepository)
    {
        $products = $this->getMyProducts($productRepository);
        return $this->render(
            '/seller/show_my_products.html.twig',
            [
                'products' => $products
            ]
        );
    }

    /**
     * @param  ProductRepository $productRepository
     * @return array
     */
    public function getMyProducts(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy(
            [
                'user' => $this->getUser()->getId()
            ]
        );

        return $products;
    }

    /**
     * @Route("/seller/product-visibility/{id}", name="update_product_visibility_seller")
     * @param                                    EntityManagerInterface $entityManager
     * @param                                    Product $product
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function updateProductVisibilitySeller(
        Product $product,
        EntityManagerInterface $entityManager
    ) {
        if ($product->getVisibility() === 0) {
            $product->setVisibility(1);
            $this->addFlash('success', 'Product made visible!');
        } elseif ($product->getVisibility() === 1) {
            $product->setVisibility(0);
            $this->addFlash('success', 'Product hidden!');
        } else {
            $this->addFlash('warning', 'Something went wrong');
        }

        $entityManager->flush();
        return $this->redirectToRoute('show_my_products');
    }

    /**
     * @Route("/seller/update-product-info/{id}", name="update_my_product_info")
     * @param                                     EntityManagerInterface $entityManager
     * @param                                     ProductCategoryRepository $productCategoryRepository
     * @param                                     ProductService $productService
     * @param                                     Request $request
     * @param                                     Product $product
     * @return                                    \Symfony\Component\HttpFoundation\Response
     */
    public function updateMyProductInfo(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductCategoryRepository $productCategoryRepository,
        ProductService $productService
    ) {
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }

        $productIm = $product->getImage();
        $productUrlNum = substr($product->getCustomUrl(), -9);
        $productUrl = substr($product->getCustomUrl(), 0, -9);
        $product->setCustomUrl($productUrl);
        $product->setImage(
            new File($this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage())
        );
        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $customUrl = $product->getCustomUrl();
            $pageName = $productService->changeCustomUrl($customUrl, $product, $productUrlNum);
            $product->setCustomUrl(str_replace(' ', '-', $pageName));
            $product->setImage($productIm);
            $allProductsFromProductCategory = $productCategoryRepository->findBy(
                [
                    'product' => $product->getId()
                ]
            );
            foreach ($allProductsFromProductCategory as $oneProductsFromProductCategory) {
                $entityManager->remove($oneProductsFromProductCategory);
                $entityManager->flush();
            }

            $entityManager->merge($product);
            $entityManager->flush();
            $this->addFlash('success', 'Updated the Product Info!');
            return $this->redirectToRoute('show_my_products');
        }

        return $this->render(
            '/seller/update_my_product_info.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/seller/update-product-quantity/{id}", name="update_my_product_quantity")
     * @param                                         EntityManagerInterface $entityManager
     * @param                                         WishlistRepository $wishlistRepository
     * @param                                         Request $request
     * @param                                         Product $product
     * @return                                        \Symfony\Component\HttpFoundation\Response
     */
    public function updateMyProductAvailableQuantity(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository
    ) {
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }

        $productIm = $product->getImage();
        $productBeforeQuantity = $product->getAvailableQuantity();
        $product->setImage(
            new File($this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage())
        );
        $form = $this->createForm(ProductQuantityFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $product->setImage($productIm);
            if ($productBeforeQuantity === 0 && $product->getAvailableQuantity() > 0) {
                $wishlistProducts = $wishlistRepository->findBy(
                    [
                        'product' => $product->getId()]
                );
                foreach ($wishlistProducts as $wishlistProduct) {
                    /**
                     * @var $wishlistProduct \App\Entity\Wishlist
                     */
                    $wishlistProduct->setNotify(1);
                    $entityManager->persist($wishlistProduct);
                }
            }

            $entityManager->merge($product);
            $entityManager->flush();
            $this->addFlash('success', 'Updated the Product Available Quantity!');
            return $this->redirectToRoute('show_my_products');
        }

        return $this->render(
            '/seller/update_my_product_quantity.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/seller/update-product-image/{id}", name="update_my_product_image")
     * @param                                      EntityManagerInterface $entityManager
     * @param                                      ProductService $productService
     * @param                                      Request $request
     * @param                                      Product $product
     * @return                                     \Symfony\Component\HttpFoundation\Response
     */
    public function updateMyProductImage(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductService $productService
    ) {
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }

        $product->setImage(
            new File($this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage())
        );
        $form = $this->createForm(ProductImageFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $file = $product->getImage();
            $fileName = $productService->generateUniqueFileName() . '.' . $file->guessExtension();
            try {
                $file->move(
                    $this->getParameter('image_directory'),
                    $fileName
                );
            } catch (FileException $e) {
                // ... handle exception if something happens during file upload
            }

            $product->setImage($fileName);
            $entityManager->merge($product);
            $entityManager->flush();
            $this->addFlash('success', 'Updated the Product Image!');
            return $this->redirectToRoute('show_my_products');
        }

        return $this->render(
            '/seller/update_my_product_image.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/seller/handle-search-per-user/{_query?}", name="handle_search", methods={"POST", "GET"})
     * @var                                     $_query
     * @param                                   ProductService $productService
     * @return                                  JsonResponse
     */
    public function handleSearchRequestPerUserSeller($_query, ProductService $productService)
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
     * @Route("/seller/ajax-person-sold-user-seller/{id?}", name="ajax_person_sold_seller_user")
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
        $soldPerUser = [];
        if ($id) {
            $userName = $id->getFullName();
            /**
             * @var Product $product
             */
            $soldPerUser = $soldRepository->getSoldProductPerUser($id, $products);
        } else {
            $userName = "All users";
            /**
             * @var Product $product
             */
            $soldPerUser[] = $soldRepository->findBy(
                [
                    'product' => $products
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        }

        return new JsonResponse(
            [
                'soldItems' => $soldPerUser,
                'userName' => $userName,
            ]
        );
    }

    /**
     * @Route("/seller/sold-items-per-user/{id?}", name="sold_items_per_user")
     * @param                                User $id
     * @param                                SoldRepository $soldRepository
     * @param                                ProductRepository $productRepository
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function listOfPeopleThatBoughtMyProduct(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null
    ) {
        $products = $productRepository->findBy(
            [
                'user' => $this->getUser()->getId()
            ]
        );
        if ($id) {
            /**
             * @var User $userId
             */
            $userName = $id->getFullName();
            /**
             * @var Product $product
             */
            $soldPerUser = $soldRepository->findBy(
                [
                    'user' => $id,
                    'product' => $products
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        } else {
            $userName = "All users";
            /**
             * @var Product $product
             */
            $soldPerUser = $soldRepository->findBy(
                [
                    'product' => $products
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        }

        return $this->render(
            '/seller/list_of_sold_items_per_user.html.twig',
            [
                'soldItems' => $soldPerUser,
                'userName' => $userName
            ]
        );
    }


    /**
     * @Route("/seller/confirm-buy-per-user-seller/{id}", name="confirm_buy_per_user")
     * @param                                        EntityManagerInterface $entityManager
     * @param                                        Sold $sold
     * @return                                       \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerUser(
        Sold $sold,
        EntityManagerInterface $entityManager
    ) {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('sold_items_per_user');
        }

        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        $entityManager->flush();
        return $this->redirectToRoute(
            'sold_items_per_user',
            [
                'id' => $sold->getUser()->getId()]
        );
    }

    /**
     * @Route("/seller/delete-sold-item-per-user-seller/{id}", name="delete_sold_item_per_user")
     * @param                                           ProductRepository $productRepository
     * @param                                           EntityManagerInterface $entityManager
     * @param                                           WishlistRepository $wishlistRepository
     * @param                                           ProductService $productService
     * @param                                           Sold $sold
     * @return                                          \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerUser(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository,
        ProductService $productService
    ) {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('sold_items_per_user');
        }

        $productService->deleteProductItem($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'sold_items_per_user',
            [
                'id' => $sold->getUser()->getId()]
        );
    }

    /**
     * @Route("/seller/sold-items-per-product", name="list_of_sold_items_per_product")
     * @param                                   Request $request
     * @param                                   SoldRepository $soldRepository
     * @param                                   ProductRepository $productRepository
     * @return                                  \Symfony\Component\HttpFoundation\Response
     */
    public function listOfBoughtItemsPerProduct(
        Request $request,
        SoldRepository $soldRepository,
        ProductRepository $productRepository
    ) {
        $products = $productRepository->findBy(
            [
                'user' => $this->getUser()->getId()
            ]
        );
        $sold = new Sold();
        $form = $this->createForm(ListOfBoughtItemsPerProductFormType::class, $sold, array("user" => $this->getUser()));
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData()->getProduct();
            $message = $product->getName();
            $listForProduct = $soldRepository->findBy(
                [
                    'product' => $product->getId()
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        } else {
            $message = "All products";
            $listForProduct = $soldRepository->findBy(
                [
                    'product' => $products
                ],
                [
                    'boughtAt' => 'DESC'
                ]
            );
        }

        return $this->render(
            '/seller/list_of_sold_items_per_product.html.twig',
            [
                'form' => $form->createView(),
                'soldItems' => $listForProduct,
                'message' => $message
            ]
        );
    }

    /**
     * @Route("/seller/confirm-buy-per-product/{id}", name="confirm_buy_per_product")
     * @param                                         EntityManagerInterface $entityManager
     * @param                                         Sold $sold
     * @return                                        \Symfony\Component\HttpFoundation\Response
     */
    public function confirmBuyPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager
    ) {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('list_of_sold_items_per_product');
        }

        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        $entityManager->flush();
        return $this->redirectToRoute(
            'list_of_sold_items_per_product',
            [
                'id' => $this->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/seller/delete-sold-item-per-product/{id}", name="delete_sold_item_per_product")
     * @param                                              ProductRepository $productRepository
     * @param                                              EntityManagerInterface $entityManager
     * @param                                              ProductService $productService
     * @param                                              WishlistRepository $wishlistRepository
     * @param                                              Sold $sold
     * @return                                             \Symfony\Component\HttpFoundation\Response
     */
    public function deleteSoldItemPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository,
        ProductRepository $productRepository,
        ProductService $productService
    ) {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('list_of_sold_items_per_product');
        };
        $productService->deleteProductItem($sold, $entityManager, $productRepository, $wishlistRepository);
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute(
            'list_of_sold_items_per_product',
            [
                'id' => $this->getUser()->getId()
            ]
        );
    }

    /**
     * @Route("/seller/sold-product/{id}", name="view_sold_product_info")
     * @param                              Sold $sold
     * @return                             \Symfony\Component\HttpFoundation\Response
     */
    public function viewSoldProductInfo(Sold $sold)
    {
        return $this->render(
            '/seller/view_sold_item.html.twig',
            [
                'sold' => $sold
            ]
        );
    }
}
