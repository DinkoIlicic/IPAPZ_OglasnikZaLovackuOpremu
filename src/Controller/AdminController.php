<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;
use App\Entity\Category;
use App\Entity\Seller;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Wishlist;
use App\Form\CategoryFormType;
use App\Form\PasswordFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductImageFormType;
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Form\AdminListOfCategoriesFormType;
use App\Form\ProductQuantityFormType;
use App\Form\ProfileFormType;
use App\Repository\CategoryRepository;
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
     * @Route("/admin/appliers", name="checkapplyforseller")
     * @param SellerRepository $sellerRepository
     * @return Response
     */
    public function listAllAppliersForSeller(SellerRepository $sellerRepository)
    {
        $sellers = $sellerRepository->findBy([], [
            'id' => 'DESC'
        ]);
        $message = "List of all appliers: ";
        return $this->render('admin/listofallappliers.html.twig', [
            'message' => $message,
            'sellers' => $sellers
        ]);
    }

    /**
     * @Route("/admin/applier/{id}", name="checkoneapplierforseller")
     * @param SellerRepository $sellerRepository
     * @param Seller $seller
     * @return Response
     */
    public function listOneApplierForSeller(Seller $seller, SellerRepository $sellerRepository)
    {
        $sell = $sellerRepository->findOneBy(['id' => $seller->getId()]);
        return $this->render('admin/viewapplier.html.twig', [
            'seller' => $sell,
            'verified' => $sell->getVerified()
        ]);
    }

    /**
     * @Route("/admin/verifyapplier/{id}", name="verifyapplier")
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
        return $this->redirectToRoute('checkoneapplierforseller', [
            'id' => $seller->getId()
        ]);
    }

    /**
     * @Route("/admin/categories", name="listofcategories")
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
            return $this->redirectToRoute('listofcategories');
        }
        return $this->render('admin/categorylist.html.twig', [
            'form' => $form->createView(),
            'message' => '',
            'categories' => $allCategories
        ]);
    }

    /**
     * @Route("/admin/updatecategory/{id}", name="checkonecategory")
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
            return $this->redirectToRoute('listofcategories');
        }
        return $this->render('admin/viewcategory.html.twig', [
            'form' => $form->createView(),
            'category' => $category,
        ]);
    }

    /**
     * @Route("/admin/catvisibility/{id}", name="categoryvisibilityadmin")
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
            return $this->redirectToRoute('listofcategories');
        } elseif ($category->getVisibilityAdmin() === 1) {
            $category->setVisibilityAdmin(0);
            $entityManager->flush();
            $this->addFlash('success', 'Category hidden!');
            return $this->redirectToRoute('listofcategories');
        } else {
            $this->addFlash('warning', 'Something went wrong!');
            return $this->redirectToRoute('listofcategories');
        }
    }

    /**
     * @Route("/admin/products", name="listofproducts")
     * @param ProductRepository $productRepository
     * @param Request $request
     * @return Response
     */
    public function showAllProducts(
        Request $request,
        ProductRepository $productRepository)
    {
        $products = [];
        $form = $this->createForm(AdminListOfCategoriesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $category = $form->getData();
            /**
             * @var Category $category
             */
            $message = $category->getId()->getName();
            $products[] = $productRepository->getProductsFromCategory($category->getId());
        } else {
            $products[] = $productRepository->findBy([], [
                'name' => 'ASC'
            ]);
            $message = "All categories";
        }
        return $this->render('admin/viewproducts.html.twig', [
            'form' => $form->createView(),
            'products' => $products,
            'message' => $message
        ]);
    }

    /**
     * @Route("/admin/updateproductinfo/{id}", name="updateproductinfo")
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
            return $this->redirectToRoute('listofproducts');
        }
        return $this->render('admin/updateproductinfo.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/updateproductquantity/{id}", name="updateproductquantity")
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
            return $this->redirectToRoute('listofproducts');
        }
        return $this->render('admin/updateproductquantity.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/updateproductimage/{id}", name="updateproductimage")
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
            return $this->redirectToRoute('listofproducts');
        }
        return $this->render('admin/updateproductimage.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/prodvisibility/{id}", name="updateproductvisibilityadmin")
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
            return $this->redirectToRoute('listofproducts');
        } elseif ($product->getVisibilityAdmin() === 1) {
            $product->setVisibilityAdmin(0);
            $entityManager->flush();
            $this->addFlash('success', 'Product hidden!');
            return $this->redirectToRoute('listofproducts');
        } else {
            $this->addFlash('warning', 'Something went wrong');
            return $this->redirectToRoute('listofproducts');
        }
    }

    /**
     * @Route("/admin/users", name="listofusers")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function listOfUsers(UserRepository $userRepository)
    {
        $listOfUsers = $userRepository->findBy([], [
            'fullName' => 'ASC'
        ]);
        return $this->render('admin/viewusers.html.twig', [
            'users' => $listOfUsers
        ]);
    }

    /**
     * @Route("/admin/userinfo/{id}", name="newuserinfoadmin")
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
            return $this->redirectToRoute('listofusers');
        }
        return $this->render('admin/updateuserinfo.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/admin/usernewpassword/{id}", name="newpasswordadmin")
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
            return $this->redirectToRoute('listofusers');
        }
        return $this->render('admin/updateuserpassword.html.twig', [
            'profileForm' => $form->createView(),
        ]);
    }

    /**
     * @return Response
     */
    public function searchBar()
    {
        $form = $this->createFormBuilder(null)
            ->setAction($this->generateUrl('handle_search'))
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
     * @Route("/admin/handleSearch/{_query?}", name="handle_search", methods={"POST", "GET"})
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
     * @Route("/admin/itemsoldperuser/{id?}", name="viewpeopleitemsperperson")
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
        $soldPerUser = [];
        if ($id) {
            $message = $id->getFullName();
            /**
             * @var Product $product
             */
            $soldPerUser[] = $soldRepository->findBy([
                'user' => $id,
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $message = "All users";
            /**
             * @var Product $product
             */
            $soldPerUser[] = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('admin/viewpeopleitemsperperson.html.twig', [
            'solditems' => $soldPerUser,
            'message' => $message,
            'controller_name' => 'HomeController',
        ]);
    }

    /**
     * @Route("/admin/confirmbuyperpersonadmin/{id}", name="confirmbuyperpersonadmin")
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

        return $this->redirectToRoute('viewpeopleitemsperperson');
    }

    /**
     * @Route("/admin/confirmbuyperproductadmin/{id}", name="confirmbuyperproductadmin")
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

        return $this->redirectToRoute('viewpeopleitemsperproduct');
    }

    /**
     * @Route("/seller/deletesolditemperpersonadmin/{id}", name="deletesolditemperpersonadmin")
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
        return $this->redirectToRoute('viewpeopleitemsperperson');
    }

    /**
     * @Route("/seller/deletesolditemperproductadmin/{id}", name="deletesolditemperproductadmin")
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
        return $this->redirectToRoute('viewpeopleitemsperproduct');
    }

    /**
     * @Route("/admin/itemsoldperproduct", name="viewpeopleitemsperproduct")
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
        $listOfSoldItems = [];
        $form = $this->createForm(AdminListOfBoughtItemsPerProductFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData()->getProduct();
            $message = $product->getName();
            $listOfSoldItems[] = $soldRepository->findBy([
                'product' => $product->getId()
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $message = "All products";
            $listOfSoldItems[] = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('admin/viewpeopleitemsperproduct.html.twig', [
            'form' => $form->createView(),
            'solditems' => $listOfSoldItems,
            'message' => $message
        ]);
    }

    /**
     * @Route("/admin/soldproduct/{id}", name="viewsoldproductinfoadmin")
     * @param Sold $sold
     * @return Response
     */
    public function viewSoldProductInfo(Sold $sold)
    {
        return $this->render('admin/viewsolditem.html.twig', [
            'sold' => $sold
        ]);
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}