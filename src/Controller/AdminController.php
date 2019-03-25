<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Coupon;
use App\Entity\CouponCodes;
use App\Entity\CustomPage;
use App\Entity\PaymentMethod;
use App\Entity\PaymentTransaction;
use App\Entity\Product;
use App\Entity\RandomCodeGenerator;
use App\Entity\Seller;
use App\Entity\Sold;
use App\Entity\User;
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Form\AdminListOfCategoriesFormType;
use App\Form\CategoryFormType;
use App\Form\CouponCodesFormType;
use App\Form\CouponFormType;
use App\Form\CustomPageFormType;
use App\Form\PasswordFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductQuantityFormType;
use App\Form\ProfileFormType;
use App\Form\RemoveCouponCodesFormType;
use App\Repository\CategoryRepository;
use App\Repository\CouponCodesRepository;
use App\Repository\CouponRepository;
use App\Repository\CustomPageRepository;
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
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
     * @Route("/admin/add-page", name="add_custom_page_admin")
     * @param                    Request $request
     * @param                    EntityManagerInterface $entityManager
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function addCustomPage(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CustomPageFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var $customPage CustomPage
             */
            $customPage = $form->getData();
            if ($customPage->getContent() === null) {
                $this->addFlash('warning', 'Content field can not be empty!');
                return $this->redirectToRoute('view_custom_pages_admin');
            }

            $customPage->setPageName(str_replace(' ', '-', $customPage->getPageName()));
            $customPage->setVisibilityAdmin(1);
            $entityManager->persist($customPage);
            $entityManager->flush();
            $this->addFlash('success', 'Page added!');
            return $this->redirectToRoute('view_custom_pages_admin');
        }

        return $this->render(
            '/admin/add_custom_page.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/view-pages", name="view_custom_pages_admin")
     * @param                      CustomPageRepository $customPageRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function viewCustomPages(CustomPageRepository $customPageRepository)
    {
        $allCustomPages = $customPageRepository->findBy([], ['id' => 'DESC']);
        return $this->render(
            '/admin/view_custom_pages.html.twig',
            [
                'allCustomPages' => $allCustomPages,
            ]
        );
    }

    /**
     * @Route("/admin/delete-page/{id}", name="delete_custom_page_admin")
     * @param                            CustomPage $customPage
     * @param                            EntityManagerInterface $entityManager
     * @return                           \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCustomPage(EntityManagerInterface $entityManager, CustomPage $customPage)
    {
        $entityManager->remove($customPage);
        $entityManager->flush();
        $this->addFlash('success', 'Page deleted!');
        return $this->redirectToRoute('view_custom_pages_admin');
    }

    /**
     * @Route("/admin/edit-page/{id}", name="edit_custom_page_admin")
     * @param                          CustomPage $customPage
     * @param                          EntityManagerInterface $entityManager
     * @param                          Request $request
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function editCustomPage(CustomPage $customPage, EntityManagerInterface $entityManager, Request $request)
    {
        $form = $this->createForm(CustomPageFormType::class, $customPage);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var $customPage CustomPage
             */
            $customPage = $form->getData();
            $customPage->setPageName(str_replace(' ', '-', $customPage->getPageName()));
            $entityManager->persist($customPage);
            $entityManager->flush();
            $this->addFlash('success', 'Page edited!');
            return $this->redirectToRoute('view_custom_pages_admin');
        }

        return $this->render(
            '/admin/edit_custom_page.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/visibility-page/{id}", name="visibility_custom_page_admin")
     * @param                                EntityManagerInterface $entityManager
     * @param                                CustomPage $customPage
     * @return                               \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateVisibilityCustomPage(EntityManagerInterface $entityManager, CustomPage $customPage)
    {
        if ($customPage->getVisibilityAdmin() === 0) {
            $customPage->setVisibilityAdmin(1);
            $this->addFlash('success', 'Page made visible!');
        } elseif ($customPage->getVisibilityAdmin() === 1) {
            $customPage->setVisibilityAdmin(0);
            $this->addFlash('success', 'Page hidden!');
        } else {
            $this->addFlash('warning', 'Something went wrong');
        }

        $entityManager->flush();
        return $this->redirectToRoute('view_custom_pages_admin');
    }

    /**
     * @Route("/admin/coupons/", name="show_coupons")
     * @param                    CouponRepository $couponRepository
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function showCouponGroup(CouponRepository $couponRepository)
    {
        $coupons = $couponRepository->findAll();

        return $this->render(
            '/admin/coupons.html.twig',
            [
                'coupons' => $coupons,
            ]
        );
    }

    /**
     * @Route("/admin/add-coupon/", name="add_coupon_group")
     * @param                       Request $request
     * @param                       EntityManagerInterface $entityManager
     * @return                      \Symfony\Component\HttpFoundation\Response
     */
    public function addCouponGroup(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CouponFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $couponGroup = $form->getData();
            $entityManager->persist($couponGroup);
            $entityManager->flush();
            $this->addFlash('success', 'Coupon group added!');
            return $this->redirectToRoute('show_coupons');
        };

        return $this->render(
            '/admin/add_coupon_group.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/add-coupon-codes/{id}", name="add_coupon_codes")
     * @param                                 Request $request
     * @param                                 EntityManagerInterface $entityManager
     * @param                                 Coupon $coupon
     * @return                                \Symfony\Component\HttpFoundation\Response
     */
    public function addCouponCodes(Request $request, EntityManagerInterface $entityManager, Coupon $coupon)
    {
        $form = $this->createForm(CouponCodesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $codesAmount = $form->get('amount')->getData();
            if (!($codesAmount >= 1) || !($codesAmount <= 10000)) {
                $this->addFlash('warning', 'Amount must be between 1 and 10000!');
                return $this->redirectToRoute(
                    'add_coupon_codes',
                    [
                        'id' => $coupon->getId()]
                );
            }

            $codesAll = $form->get('allProducts')->getData();
            $codesCategory = $form->get('category')->getData();
            $codesProduct = $form->get('product')->getData();
            if (!$codesAll && $codesCategory === null && $codesProduct === null) {
                $this->addFlash(
                    'warning',
                    'Please choose at least one of the 3 given options (All products, category or product)!'
                );
                return $this->redirectToRoute(
                    'add_coupon_codes',
                    [
                        'id' => $coupon->getId()]
                );
            }

            $codesNames = new RandomCodeGenerator();
            $codesArrayNames = $codesNames->generate($codesAmount);
            for ($i = 0; $i < $codesAmount; $i++) {
                $code = new CouponCodes();
                $code->setCodeGroup($coupon);
                $code->setCodeName($codesArrayNames[$i]);
                if ($codesAll) {
                    $code->setAllProducts(1);
                    $code->setProductId(0);
                    $code->setCategoryId(0);
                } elseif ($codesCategory !== null) {
                    $code->setCategoryId($codesCategory->getId());
                    $code->setAllProducts(0);
                    $code->setProductId(0);
                } elseif ($codesProduct !== null) {
                    $code->setProductId($codesProduct->getId());
                    $code->setAllProducts(0);
                    $code->setCategoryId(0);
                }

                $code->setDiscount($coupon->getDiscount());
                $entityManager->persist($code);
            }

            $entityManager->flush();
            $this->addFlash(
                'success',
                'Coupons added!'
            );
            return $this->redirectToRoute(
                'show_coupons'
            );
        };

        return $this->render(
            '/admin/add_coupon_codes.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/delete-coupon-codes/{id}", name="delete_coupon_codes")
     * @param                                    Request $request
     * @param                                    Coupon $coupon
     * @param                                    EntityManagerInterface $entityManager
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCouponCodes(Request $request, Coupon $coupon, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(RemoveCouponCodesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $startId = $form->get('startId')->getData();
            $endId = $form->get('endId')->getData();
            $deleteQuery = $entityManager->createQuery(
                '
                Delete 
                From App\Entity\CouponCodes cc
                WHERE cc.id >= :startId AND cc.id <= :endId AND cc.codeGroup = :coupon
                '
            );
            $deleteQuery->setParameter('startId', $startId);
            $deleteQuery->setParameter('endId', $endId);
            $deleteQuery->setParameter('coupon', $coupon);
            $deleteQuery->execute();
            $this->addFlash(
                'success',
                'Coupon codes removed!'
            );
            return $this->redirectToRoute(
                'show_coupons'
            );
        };

        return $this->render(
            '/admin/remove_coupon_codes.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/excel-coupon-codes/{id}", name="create_excel_coupon_codes_file")
     * @return              \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws              \PhpOffice\PhpSpreadsheet\Exception
     * @throws              \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @param               CouponCodesRepository $couponCodesRepository
     * @param               Coupon $coupon
     */
    public function excelCouponCodes(CouponCodesRepository $couponCodesRepository, Coupon $coupon)
    {
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(
            new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder()
        );
        $spreadsheet = new Spreadsheet();
        $couponCodes = $couponCodesRepository->findBy(['codeGroup' => $coupon], ['id' => 'ASC']);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Id');
        $sheet->setCellValue('B1', 'Coupon Code Name');
        $sheet->setCellValue('C1', 'Coupon Group');
        $sheet->setCellValue('D1', 'Discount');
        $sheet->setCellValue('E1', 'All Products');
        $sheet->setCellValue('F1', 'Category');
        $sheet->setCellValue('G1', 'Product');
        $i = 2;
        foreach ($couponCodes as $item) {
            /**
             * @var CouponCodes $item
             */
            $sheet->setCellValue('A' . $i, $item->getId());
            $sheet->setCellValue('B' . $i, $item->getCodeName());
            $sheet->setCellValue('C' . $i, $item->getCodeGroup()->getCodeGroupName());
            $sheet->setCellValue('D' . $i, $item->getDiscount());
            $sheet->setCellValue('E' . $i, $item->getAllProducts());
            $sheet->setCellValue('F' . $i, $item->getCategoryId());
            $sheet->setCellValue('G' . $i, $item->getProductId());
            $i++;
        }

        $sheet->setTitle("Coupon Codes");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'coupon_codes.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($tempFile);

        // Return the excel file as an attachment
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
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


    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}
