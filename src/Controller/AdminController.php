<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;
use App\Entity\Category;
use App\Entity\CustomPage;
use App\Entity\Seller;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Wishlist;
use App\Form\CategoryFormType;
use App\Form\CustomPageFormType;
use App\Form\PasswordFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductImageFormType;
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Form\AdminListOfCategoriesFormType;
use App\Form\ProductQuantityFormType;
use App\Form\ProfileFormType;
use App\Repository\CategoryRepository;
use App\Repository\CustomPageRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\SellerRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\UserRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/", name="admin_index")
     * @return Response
     */
    public function index()
    {
        return $this->render('admin/index.html.twig', [
        ]);
    }

    /**
     * @Route("/admin/appliers", name="check_apply_for_seller")
     * @param SellerRepository $sellerRepository
     * @return Response
     */
    public function listAllAppliersForSeller(SellerRepository $sellerRepository)
    {
        $sellers = $sellerRepository->findBy([], [
            'id' => 'DESC'
        ]);
        $message = "List of all appliers: ";
        return $this->render('/admin/list_of_all_appliers.html.twig', [
            'message' => $message,
            'sellers' => $sellers
        ]);
    }

    /**
     * @Route("/admin/applier/{id}", name="check_one_applier_for_seller")
     * @param SellerRepository $sellerRepository
     * @param Seller $seller
     * @return Response
     */
    public function listOneApplierForSeller(Seller $seller, SellerRepository $sellerRepository)
    {
        $sell = $sellerRepository->findOneBy(['id' => $seller->getId()]);
        return $this->render('/admin/view_applier.html.twig', [
            'seller' => $sell,
            'verified' => $sell->getVerified()
        ]);
    }

    /**
     * @Route("/admin/verify-applier/{id}", name="verify_applier")
     * @param Seller $seller
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function verifyApplier(Seller $seller, EntityManagerInterface $entityManager)
    {
        if ($seller->getVerified() === 0) {
            $seller->setVerified(1);
            $seller->getUser()->setRoles(['ROLE_SELLER']);
            $entityManager->flush();
            $this->addFlash('success', 'Applier verified!');
        } elseif ($seller->getVerified() === 1) {
            $seller->setVerified(0);
            $seller->getUser()->setRoles(['ROLE_USER']);
            $entityManager->flush();
            $this->addFlash('success', 'Applier unverified!');
        }
        return $this->redirectToRoute('check_one_applier_for_seller', [
            'id' => $seller->getId()
        ]);
    }

    /**
     * @Route("/admin/categories", name="list_of_categories")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function listOfAllCategories(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository)
    {
        $allCategories = $categoryRepository->findBy([], [
            'name' => 'ASC'
        ]);
        $form = $this->createForm(CategoryFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /** @var Category $category */
            $category = $form->getData();
            $category->setUser($this->getUser());
            $category->setVisibility(1);
            $category->setVisibilityAdmin(1);
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Inserted new category!');
            return $this->redirectToRoute('list_of_categories');
        }
        return $this->render('/admin/category_list.html.twig', [
            'form' => $form->createView(),
            'message' => '',
            'categories' => $allCategories
        ]);
    }

    /**
     * @Route("/admin/update-category/{id}", name="check_one_category")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function updateOneCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Updated category!');
            return $this->redirectToRoute('list_of_categories');
        }
        return $this->render('/admin/view_category.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/admin/cat-visibility/{id}", name="category_visibility_admin")
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     */
    public function updateCategoryVisibilityAdmin(
        Category $category,
        EntityManagerInterface $entityManager)
    {
        if ($category->getVisibilityAdmin() === 0) {
            $category->setVisibilityAdmin(1);
            $entityManager->flush();
            $this->addFlash('success', 'Category made visible!');
            return $this->redirectToRoute('list_of_categories');
        } elseif ($category->getVisibilityAdmin() === 1) {
            $category->setVisibilityAdmin(0);
            $entityManager->flush();
            $this->addFlash('success', 'Category hidden!');
            return $this->redirectToRoute('list_of_categories');
        } else {
            $this->addFlash('warning', 'Something went wrong!');
            return $this->redirectToRoute('list_of_categories');
        }
    }

    /**
     * @Route("/admin/products", name="list_of_products")
     * @param ProductRepository $productRepository
     * @param Request $request
     * @return Response
     */
    public function showAllProducts(
        Request $request,
        ProductRepository $productRepository)
    {
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
            $products = $productRepository->findBy([], [
                'name' => 'ASC'
            ]);
            $message = "All categories";
        }
        return $this->render('/admin/view_products.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
            'message' => $message
        ]);
    }

    /**
     * @Route("/admin/update-product-info/{id}", name="update_product_info")
     * @param EntityManagerInterface $entityManager
     * @param ProductCategoryRepository $productCategoryRepository
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateProductInfo(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductCategoryRepository $productCategoryRepository)
    {
        $productIm = $product->getImage();
        $productUrlNum = substr($product->getCustomUrl(), -9);
        $productUrl = substr($product->getCustomUrl(), 0, -9);
        $product->setCustomUrl($productUrl);
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $customUrl = $product->getCustomUrl();
            if(empty($customUrl)) {
                $customUrl = $product->getName();
                $productUrlNum = '-' . rand(10000000,99999999);
            }
            $pageName = $customUrl . $productUrlNum;
            $product->setCustomUrl(str_replace(' ', '-', $pageName));
            $product->setImage($productIm);
            $allProductsFromProductCategory = $productCategoryRepository->findBy([
                'product' => $product->getId()
            ]);
            foreach ($allProductsFromProductCategory as $oneProductsFromProductCategory) {
                $entityManager->remove($oneProductsFromProductCategory);
                $entityManager->flush();
            }
            $entityManager->merge($product);
            $entityManager->flush();
            $this->addFlash('success', 'Updated the Product Info!');
            return $this->redirectToRoute('list_of_products');
        }
        return $this->render('/admin/update_product_info.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/update-product-quantity/{id}", name="update_product_quantity")
     * @param EntityManagerInterface $entityManager
     * @param WishlistRepository $wishlistRepository
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateProductQuantity(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository)
    {
        $productIm = $product->getImage();
        $productBeforeQuantity = $product->getAvailableQuantity();
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));

        $form = $this->createForm(ProductQuantityFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $product->setImage($productIm);
            if ($productBeforeQuantity === 0 && $product->getAvailableQuantity() > 0) {
                $wishlistProducts = $wishlistRepository->findBy([
                    'product' => $product->getId()]);
                foreach ($wishlistProducts as $wishlistProduct) {
                    /**
                     * @var $wishlistProduct Wishlist
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
        return $this->render('/admin/update_product_quantity.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/update-product-image/{id}", name="update_product_image")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateMyProductImage(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager)
    {
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductImageFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $file = $product->getImage();
            $fileName = $this->generateUniqueFileName().'.'.$file->guessExtension();
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
        return $this->render('/admin/update_product_image.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/prod-visibility/{id}", name="update_product_visibility_admin")
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     */
    public function updateProductVisibilityAdmin(
        Product $product,
        EntityManagerInterface $entityManager)
    {
        if ($product->getVisibilityAdmin() === 0) {
            $product->setVisibilityAdmin(1);
            $entityManager->flush();
            $this->addFlash('success', 'Product made visible!');
            return $this->redirectToRoute('list_of_products');
        } elseif ($product->getVisibilityAdmin() === 1) {
            $product->setVisibilityAdmin(0);
            $entityManager->flush();
            $this->addFlash('success', 'Product hidden!');
            return $this->redirectToRoute('list_of_products');
        } else {
            $this->addFlash('warning', 'Something went wrong');
            return $this->redirectToRoute('list_of_products');
        }
    }

    /**
     * @Route("/admin/users", name="list_of_users")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function listOfUsers(UserRepository $userRepository)
    {
        $listOfUsers = $userRepository->findBy([], [
            'fullName' => 'ASC'
        ]);
        return $this->render('/admin/view_users.html.twig', [
            'users' => $listOfUsers
        ]);
    }

    /**
     * @Route("/admin/user-info/{id}", name="new_user_info_admin")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param User $user
     * @return Response
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
        return $this->render('/admin/update_user_info.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/user-new-password/{id}", name="new_password_admin")
     * @param UserPasswordEncoderInterface $passwordEncoder
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param User $user
     * @return Response
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
        return $this->render('/admin/update_user_password.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     */
    public function searchBar()
    {
        $form = $this->createFormBuilder(null)
            ->add("query", TextType::class, [
                'attr' => [
                    'placeholder'   => 'Enter user name'
                ],
                'label' => 'User'
            ])
            ->getForm()
        ;
        return $this->render('advertisement/search.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/handle-search/{_query?}", name="handle_search", methods={"POST", "GET"})
     * @var $_query
     * @return JsonResponse
     */
    public function handleSearchRequest($_query)
    {
        $em = $this->getDoctrine()->getManager();
        if ($_query)
        {
            $data = $em->getRepository(User::class)->findByName($_query);
        } else {
            $data = $em->getRepository(User::class)->findAll();
        }

        // setting up the serializer
        $normalizers = [
            new ObjectNormalizer()
        ];
        $encoders =  [
            new JsonEncoder()
        ];
        $serializer = new Serializer($normalizers, $encoders);
        $jsonObject = $serializer->serialize($data, 'json', [
            'circular_reference_handler' => function ($user) {
                /**
                 * @var $user User
                 */
                return $user->getId();
            }
        ]);

        return new JsonResponse($jsonObject, 200, [], true);
    }

    /**
     * @Route("/admin/ajax-person-sold/{id?}", name="ajax_person_sold")
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param User $id
     * @return Response
     */
    public function ajaxListPerson(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null)
    {
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
            $soldPerUser[] = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }

        return new JsonResponse([
            'soldItems' => $soldPerUser,
            'userName' => $userName,
        ]);
    }

    /**
     * @Route("/admin/item-sold-per-user/{id?}", name="view_sold_items_per_person")
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param User $id
     * @return Response
     */
    public function listOfPeopleThatBoughtMyProduct(
        SoldRepository $soldRepository,
        ProductRepository $productRepository,
        User $id = null)
    {
        $products = $productRepository->findAll();
        if ($id) {
            $userName = $id->getFullName();
            /**
             * @var Product $product
             */
            $soldPerUser = $soldRepository->findBy([
                'user' => $id,
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $userName = "All users";
            /**
             * @var Product $product
             */
            $soldPerUser = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('/admin/view_sold_items_per_person.html.twig', [
            'soldItems' => $soldPerUser,
            'userName' => $userName,
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/admin/confirm-buy-per-person-admin/{id}", name="confirm_buy_per_person_admin")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function confirmBuyPerPersonAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $entityManager->flush();
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $entityManager->flush();
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        return $this->redirectToRoute('view_sold_items_per_person');
    }

    /**
     * @Route("/admin/delete-sold-item-per-person-admin/{id}", name="delete_sold_item_per_person_admin")
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param WishlistRepository $wishlistRepository
     * @param Sold $sold
     * @return Response
     */
    public function deleteSoldItemPerUser(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository)
    {
        /**
         * @var Product $productOld
         */
        $productOld = $productRepository->findOneBy([
            'id' => $sold->getProduct()->getId()
        ]);
        if ($productOld->getAvailableQuantity() === 0) {
            $wishlistProducts = $wishlistRepository->findBy([
                'product' => $productOld->getId()]);
            foreach ($wishlistProducts as $wishlistProduct) {
                /**
                 * @var $wishlistProduct Wishlist
                 */
                $wishlistProduct->setNotify(1);
                $entityManager->persist($wishlistProduct);
            }
        }
        $productOld->setAvailableQuantity($productOld->getAvailableQuantity() + $sold->getQuantity());
        $entityManager->remove($sold);
        $entityManager->flush();

        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute('view_sold_items_per_person');
    }

    /**
     * @Route("/admin/item-sold-per-product", name="view_sold_items_per_product")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function listOfBoughtItemsPerProduct(
        Request $request,
        SoldRepository $soldRepository,
        ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        $form = $this->createForm(AdminListOfBoughtItemsPerProductFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData()->getProduct();
            $message = $product->getName();
            $listOfSoldItems = $soldRepository->findBy([
                'product' => $product->getId()
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $message = "All products";
            $listOfSoldItems = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('/admin/view_sold_items_per_product.html.twig', [
            'form' => $form->createView(),
            'solditems' => $listOfSoldItems,
            'message' => $message
        ]);
    }

    /**
     * @Route("/admin/confirm-buy-per-product-admin/{id}", name="confirm_buy_per_product_admin")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function confirmBuyPerProductAdmin(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $entityManager->flush();
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $entityManager->flush();
            $this->addFlash('success', 'Buy unconfirmed!');
        }

        return $this->redirectToRoute('view_sold_items_per_product');
    }

    /**
     * @Route("/seller/delete-sold-item-per-product-admin/{id}", name="delete_sold_item_per_product_admin")
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param WishlistRepository $wishlistRepository
     * @param Sold $sold
     * @return Response
     */
    public function deleteSoldItemPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        WishlistRepository $wishlistRepository)
    {
        /**
         * @var Product $productOld
         */
        $productOld = $productRepository->findOneBy([
            'id' => $sold->getProduct()->getId()
        ]);
        if ($productOld->getAvailableQuantity() === 0) {
            $wishlistProducts = $wishlistRepository->findBy([
                'product' => $productOld->getId()]);
            foreach ($wishlistProducts as $wishlistProduct) {
                /**
                 * @var $wishlistProduct Wishlist
                 */
                $wishlistProduct->setNotify(1);
                $entityManager->persist($wishlistProduct);
            }
        }
        $productOld->setAvailableQuantity($productOld->getAvailableQuantity() + $sold->getQuantity());
        $entityManager->remove($sold);
        $entityManager->flush();

        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute('view_sold_items_per_product');
    }

    /**
     * @Route("/admin/sold-product/{id}", name="view_sold_product_info_admin")
     * @param Sold $sold
     * @return Response
     */
    public function viewSoldProductInfo(Sold $sold)
    {
        return $this->render('/admin/view_sold_item.html.twig', [
            'sold' => $sold
        ]);
    }

    /**
     * @Route("/admin/add-page", name="add_custom_page_admin")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
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
           $customPage->setPageName(str_replace(' ', '-', $customPage->getPageName()));
           $customPage->setVisibilityAdmin(1);
           $entityManager->persist($customPage);
           $entityManager->flush();
            $this->addFlash('success', 'Page added!');
            return $this->redirectToRoute('view_custom_pages_admin');
        }
        return $this->render('/admin/add_custom_page.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/view-pages", name="view_custom_pages_admin")
     * @param CustomPageRepository $customPageRepository
     * @return Response
     */
    public function viewCustomPages(CustomPageRepository $customPageRepository)
    {
        $allCustomPages = $customPageRepository->findBy([],['id' => 'DESC']);
        return $this->render('/admin/view_custom_pages.html.twig', [
            'allCustomPages' => $allCustomPages,
        ]);
    }

    /**
     * @Route("/admin/delete-page/{id}", name="delete_custom_page_admin")
     * @param CustomPage $customPage
     * @param EntityManagerInterface $entityManager
     * @return Response
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
     * @param CustomPage $customPage
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @return Response
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
        return $this->render('/admin/edit_custom_page.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/visibility-page/{id}", name="visibility_custom_page_admin")
     * @param EntityManagerInterface $entityManager
     * @param CustomPage $customPage
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateVisibilityCustomPage(EntityManagerInterface $entityManager, CustomPage $customPage)
    {
        if ($customPage->getVisibilityAdmin() === 0) {
            $customPage->setVisibilityAdmin(1);
            $entityManager->flush();
            $this->addFlash('success', 'Page made visible!');
            return $this->redirectToRoute('view_custom_pages_admin');
        } elseif ($customPage->getVisibilityAdmin() === 1) {
            $customPage->setVisibilityAdmin(0);
            $entityManager->flush();
            $this->addFlash('success', 'Page hidden!');
            return $this->redirectToRoute('view_custom_pages_admin');
        } else {
            $this->addFlash('warning', 'Something went wrong');
            return $this->redirectToRoute('view_custom_pages_admin');
        }
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}