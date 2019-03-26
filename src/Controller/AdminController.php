<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\PaymentMethod;
use App\Entity\PaymentTransaction;
use App\Entity\Product;
use App\Entity\Seller;
use App\Entity\Sold;
use App\Entity\User;
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Form\AdminListOfCategoriesFormType;
use App\Form\CategoryFormType;
use App\Form\PasswordFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductQuantityFormType;
use App\Form\ProfileFormType;
use App\Repository\CategoryRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\PaymentTransactionRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SellerRepository;
use App\Repository\SoldRepository;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/", name="admin_index")
     * @return           \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render(
            'admin/index.html.twig'
        );
    }

    /**
     * @Route("/admin/appliers", name="check_apply_for_seller")
     * @param                    SellerRepository $sellerRepository
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function listAllAppliersForSeller(SellerRepository $sellerRepository)
    {
        $sellers = $sellerRepository->findBy(
            [],
            [
                'id' => 'DESC'
            ]
        );
        return $this->render(
            '/admin/list_of_all_appliers.html.twig',
            [
                'sellers' => $sellers
            ]
        );
    }

    /**
     * @Route("/admin/applier/{id}", name="check_one_applier_for_seller")
     * @param                        SellerRepository $sellerRepository
     * @param                        Seller $seller
     * @return                       \Symfony\Component\HttpFoundation\Response
     */
    public function listOneApplierForSeller(Seller $seller, SellerRepository $sellerRepository)
    {
        $sell = $sellerRepository->findOneBy(
            [
                'id' => $seller->getId()
            ]
        );
        return $this->render(
            '/admin/view_applier.html.twig',
            [
                'seller' => $sell,
                'verified' => $sell->getVerified()
            ]
        );
    }

    /**
     * @Route("/admin/verify-applier/{id}", name="verify_applier")
     * @param                               Seller $seller
     * @param                               EntityManagerInterface $entityManager
     * @return                              \Symfony\Component\HttpFoundation\Response
     */
    public function verifyApplier(Seller $seller, EntityManagerInterface $entityManager)
    {
        if ($seller->getVerified() === 0) {
            $seller->setVerified(1);
            $seller->getUser()->setRoles(['ROLE_SELLER']);
            $this->addFlash('success', 'Applier verified!');
        } elseif ($seller->getVerified() === 1) {
            $seller->setVerified(0);
            $seller->getUser()->setRoles(['ROLE_USER']);
            $this->addFlash('success', 'Applier unverified!');
        }

        $entityManager->flush();
        return $this->redirectToRoute(
            'check_one_applier_for_seller',
            [
                'id' => $seller->getId()
            ]
        );
    }

    /**
     * @Route("/admin/categories", name="list_of_categories")
     * @param                      Request $request
     * @param                      EntityManagerInterface $entityManager
     * @param                      CategoryRepository $categoryRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function listOfAllCategories(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository
    ) {
        $allCategories = $categoryRepository->findBy(
            [],
            [
                'name' => 'ASC'
            ]
        );
        $form = $this->createForm(CategoryFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Category $category
             */
            $category = $form->getData();
            $category->setUser($this->getUser());
            $category->setVisibility(1);
            $category->setVisibilityAdmin(1);
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'New category added!');
            return $this->redirectToRoute('list_of_categories');
        }

        return $this->render(
            '/admin/category_list.html.twig',
            [
                'form' => $form->createView(),
                'message' => '',
                'categories' => $allCategories
            ]
        );
    }

    /**
     * @Route("/admin/update-category/{id}", name="check_one_category")
     * @param                                EntityManagerInterface $entityManager
     * @param                                Request $request
     * @param                                Category $category
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function updateOneCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Updated category!');
            return $this->redirectToRoute('list_of_categories');
        }

        return $this->render(
            '/admin/view_category.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }

    /**
     * @Route("/admin/cat-visibility/{id}", name="category_visibility_admin")
     * @param                               EntityManagerInterface $entityManager
     * @param                               Category $category
     * @return                              \Symfony\Component\HttpFoundation\Response
     */
    public function updateCategoryVisibilityAdmin(
        Category $category,
        EntityManagerInterface $entityManager
    ) {
        if ($category->getVisibilityAdmin() === 0) {
            $category->setVisibilityAdmin(1);
            $this->addFlash('success', 'Category made visible!');
        } elseif ($category->getVisibilityAdmin() === 1) {
            $category->setVisibilityAdmin(0);
            $this->addFlash('success', 'Category hidden!');
        } else {
            $this->addFlash('warning', 'Something went wrong!');
        }

        $entityManager->flush();
        return $this->redirectToRoute('list_of_categories');
    }

    /**
     * @Route("/admin/products", name="list_of_products")
     * @param                    ProductRepository $productRepository
     * @param                    Request $request
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function showAllProducts(
        Request $request,
        ProductRepository $productRepository
    ) {
        $form = $this->createForm(AdminListOfCategoriesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            /**
             * @var Category $category
             */
            $message = $category->getId()->getName();
            $products = $productRepository->getProductsFromCategory($category->getId());
        } else {
            $products = $productRepository->findBy(
                [],
                [
                    'name' => 'ASC'
                ]
            );
            $message = "All categories";
        }

        return $this->render(
            '/admin/view_products.html.twig',
            [
                'form' => $form->createView(),
                'products' => $products,
                'message' => $message
            ]
        );
    }

    /**
     * @Route("/admin/update-product-info/{id}", name="update_product_info")
     * @param                                    EntityManagerInterface $entityManager
     * @param                                    ProductCategoryRepository $productCategoryRepository
     * @param                                    ProductService $productService
     * @param                                    Request $request
     * @param                                    Product $product
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function updateProductInfo(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductCategoryRepository $productCategoryRepository,
        ProductService $productService
    ) {
        $productIm = $product->getImage();
        $productUrlNum = substr($product->getCustomUrl(), -9);
        $productUrl = substr($product->getCustomUrl(), 0, -9);
        $product->setCustomUrl($productUrl);
        $product->setImage(
            new File($this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage())
        );
        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
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
            return $this->redirectToRoute('list_of_products');
        }

        return $this->render(
            '/admin/update_product_info.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/update-product-quantity/{id}", name="update_product_quantity")
     * @param                                        EntityManagerInterface $entityManager
     * @param                                        WishlistRepository $wishlistRepository
     * @param                                        Request $request
     * @param                                        Product $product
     * @return                                       \Symfony\Component\HttpFoundation\Response
     */
    public function updateProductQuantity(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository
    ) {
        $productIm = $product->getImage();
        $productBeforeQuantity = $product->getAvailableQuantity();
        $product->setImage(
            new File($this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage())
        );

        $form = $this->createForm(ProductQuantityFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
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
            $this->addFlash('success', 'Updated the Product Info!');
            return $this->redirectToRoute('list_of_products');
        }

        return $this->render(
            '/admin/update_product_quantity.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/update-product-image/{id}", name="update_product_image")
     * @param                                     EntityManagerInterface $entityManager
     * @param                                     Request $request
     * @param                                     Product $product
     * @return                                    \Symfony\Component\HttpFoundation\Response
     */
    public function updateMyProductImage(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $product->setImage(
            new File($this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage())
        );
        $form = $this->createForm(ProductImageFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $file = $product->getImage();
            $fileName = $this->generateUniqueFileName() . '.' . $file->guessExtension();
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
            $this->addFlash('success', 'Updated the Product Image!!');
            return $this->redirectToRoute('list_of_products');
        }

        return $this->render(
            '/admin/update_product_image.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/prod-visibility/{id}", name="update_product_visibility_admin")
     * @param                                EntityManagerInterface $entityManager
     * @param                                Product $product
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function updateProductVisibilityAdmin(
        Product $product,
        EntityManagerInterface $entityManager
    ) {
        if ($product->getVisibilityAdmin() === 0) {
            $product->setVisibilityAdmin(1);
            $this->addFlash('success', 'Product made visible!');
            return $this->redirectToRoute('list_of_products');
        } elseif ($product->getVisibilityAdmin() === 1) {
            $product->setVisibilityAdmin(0);
            $this->addFlash('success', 'Product hidden!');
            return $this->redirectToRoute('list_of_products');
        } else {
            $this->addFlash('warning', 'Something went wrong');
        }

        $entityManager->flush();
        return $this->redirectToRoute('list_of_products');
    }

    /**
     * @Route("/admin/users", name="list_of_users")
     * @param                 UserRepository $userRepository
     * @return                \Symfony\Component\HttpFoundation\Response
     */
    public function listOfUsers(UserRepository $userRepository)
    {
        $listOfUsers = $userRepository->findBy(
            [],
            [
                'fullName' => 'ASC'
            ]
        );
        return $this->render(
            '/admin/view_users.html.twig',
            [
                'users' => $listOfUsers
            ]
        );
    }

    /**
     * @Route("/admin/user-info/{id}", name="new_user_info_admin")
     * @param                          EntityManagerInterface $entityManager
     * @param                          Request $request
     * @param                          User $user
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserInfoAdmin(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setFullName($user->getFirstName() . ' ' . $user->getLastName());
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User info updated!');
            return $this->redirectToRoute('list_of_users');
        }

        return $this->render(
            '/admin/update_user_info.html.twig',
            [
                'profileForm' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/user-new-password/{id}", name="new_password_admin")
     * @param                                  UserPasswordEncoderInterface $passwordEncoder
     * @param                                  EntityManagerInterface $entityManager
     * @param                                  Request $request
     * @param                                  User $user
     * @return                                 \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserPasswordAdmin(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(PasswordFormType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Password updated!');
            return $this->redirectToRoute('list_of_users');
        }

        return $this->render(
            '/admin/update_user_password.html.twig',
            [
                'profileForm' => $form->createView(),
            ]
        );
    }

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

    /**
     * @Route("/admin/payment-methods/", name="show_payment_methods")
     * @param                       PaymentMethodRepository $paymentMethodRepository
     * @return                      \Symfony\Component\HttpFoundation\Response
     */
    public function showPaymentMethods(PaymentMethodRepository $paymentMethodRepository)
    {
        $paymentMethods = $paymentMethodRepository->findAll();
        return $this->render(
            'admin/payment_methods.html.twig',
            [
                'paymentMethods' => $paymentMethods,
            ]
        );
    }

    /**
     * @Route("/admin/enable-payment-method/{id}", name="enable_payment_method")
     * @param                       PaymentMethod $paymentMethod
     * @param                       EntityManagerInterface $entityManager
     * @return                      \Symfony\Component\HttpFoundation\Response
     */
    public function enablePaymentMethods(PaymentMethod $paymentMethod, EntityManagerInterface $entityManager)
    {
        if ($paymentMethod->getEnabled() === true) {
            $paymentMethod->setEnabled(false);
            $this->addFlash('success', 'Payment method disabled!');
        } elseif ($paymentMethod->getEnabled() === false) {
            $paymentMethod->setEnabled(true);
            $this->addFlash('success', 'Payment method enabled!');
        }

        $entityManager->persist($paymentMethod);
        $entityManager->flush();
        return $this->redirectToRoute('show_payment_methods');
    }

    /**
     * @Route("/admin/download/{fileName}",name="pdf_download")
     * @param $fileName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadPdf($fileName)
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/invoice/' . $fileName;

        $response = new Response();
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s"', $fileName)
        );
        $response->setContent(file_get_contents($filePath));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * @Route("/admin/confirm-payment-transaction/{paymentTransaction}",name="confirm_payment_transaction")
     * @param PaymentTransaction $paymentTransaction
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmInvoicePayment(PaymentTransaction $paymentTransaction, EntityManagerInterface $entityManager)
    {
        if ($paymentTransaction->getConfirmed() === true) {
            $paymentTransaction->setConfirmed(false);
            $this->addFlash('success', 'Payment transaction confirm removed!');
        } elseif ($paymentTransaction->getConfirmed() === false) {
            $paymentTransaction->setConfirmed(true);
            $paymentTransaction->onPrePersistPaidAt();
            $this->addFlash('success', 'Payment transaction confirmed!');
        }

        $entityManager->persist($paymentTransaction);
        $entityManager->flush();
        return $this->redirectToRoute(
            'view_sold_items_per_user_payment_method',
            [
                'id' => $paymentTransaction->getId()
            ]
        );
    }

    /**
     * @Route("/admin/delete-payment-transaction/{paymentTransaction}",name="delete_payment_transaction")
     * @param EntityManagerInterface $entityManager
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteInvoicePayment(EntityManagerInterface $entityManager, PaymentTransaction $paymentTransaction)
    {
        $entityManager->remove($paymentTransaction);
        $entityManager->flush();
        $this->addFlash('success', 'Invoice deleted!');
        return $this->redirectToRoute('view_sold_items_per_person');
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
