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
use App\Entity\User;
use App\Entity\Product;
use App\Form\CategoryFormType;
use App\Form\PasswordFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductImageFormType;
use App\Form\ListOfUserBoughtItemsFormType;
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Form\AdminListOfCategoriesFormType;
use App\Form\ProfileFormType;
use App\Repository\CategoryRepository;
use App\Repository\SellerRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

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
        $sellers = $sellerRepository->findAll();
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
        $seller->setVerified(1);
        $seller->getUser()->setRoles(['ROLE_SELLER']);
        $entityManager->flush();
        $this->addFlash('success', 'Applier verified!');
        return $this->redirectToRoute('checkoneapplierforseller', [
            'id' => $seller->getId()
        ]);
    }

    /**
     * @Route("/admin/unverifyapplier/{id}", name="unverifyapplier")
     * @param EntityManagerInterface $entityManager
     * @param Seller $seller
     * @return Response
     */
    public function unVerifyApplier(Seller $seller, EntityManagerInterface $entityManager)
    {
        $seller->setVerified(0);
        $seller->getUser()->setRoles(['ROLE_USER']);
        $entityManager->flush();
        $this->addFlash('success', 'Applier unverified!');
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
        $allCategories = $categoryRepository->findAll();
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
    public function listOneCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * Category $categorynew
             */
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
     * @Route("/admin/makeVisible/{id}", name="makeVisible")
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     */
    public function makeVisibleCategory(
        Category $category,
        EntityManagerInterface $entityManager)
    {
        $category->setVisibilityAdmin(1);
        $entityManager->flush();
        $this->addFlash('success', 'Category made visible!');
        return $this->redirectToRoute('listofcategories');
    }

    /**
     * @Route("/admin/makeNotVisible/{id}", name="makeNotVisible")
     * @param EntityManagerInterface $entityManager
     * @param Category $category
     * @return Response
     */
    public function makeNotVisibleCategory(
        Category $category,
        EntityManagerInterface $entityManager)
    {
        $category->setVisibilityAdmin(0);
        $entityManager->flush();
        $this->addFlash('success', 'Category made not visible!');
        return $this->redirectToRoute('listofcategories');
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
            $message = $category->getName();
            $products[] = $productRepository->findBy([
                'category' => $category->getId()
            ]);
        } else {
            $products[] = $productRepository->findBy([], [
                'category' => 'ASC'
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
     * @param Request $request
     * @param Product $productOld
     * @return Response
     */
    public function updateProductInfo(
        Product $productOld,
        Request $request,
        EntityManagerInterface $entityManager)
    {
        $product = new Product();
        $product->setName($productOld->getName());
        $product->setPrice($productOld->getPrice());
        $product->setUser($productOld->getUser());
        $product->setAvailableQuantity($productOld->getAvailableQuantity());
        $product->setCategory($productOld->getCategory());
        $product->setContent($productOld->getContent());
        $product->setImage(new File($this->getParameter('image_directory').'/'.$productOld->getImage()));

        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $product->setUser($productOld->getUser());
            $product->setVisibility($productOld->getVisibility());
            $product->setVisibilityAdmin($productOld->getVisibilityAdmin());
            $product->setId($productOld->getId());
            $product->setImage($productOld->getImage());
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
     * @Route("/seller/updateproductimage/{id}", name="updateproductimage")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $productOld
     * @return Response
     */
    public function updateMyProductImage(
        Product $productOld,
        Request $request,
        EntityManagerInterface $entityManager)
    {
        $product = new Product();
        $product->setName($productOld->getName());
        $product->setPrice($productOld->getPrice());
        $product->setImage(new File($this->getParameter('image_directory').'/'.$productOld->getImage()));
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
            $product->setId($productOld->getId());
            $product->setName($productOld->getName());
            $product->setPrice($productOld->getPrice());
            $product->setUser($productOld->getUser());
            $product->setAvailableQuantity($productOld->getAvailableQuantity());
            $product->setCategory($productOld->getCategory());
            $product->setContent($productOld->getContent());
            $product->setVisibility($productOld->getVisibility());
            $product->setVisibilityAdmin($productOld->getVisibility());
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
     * @Route("/admin/makeVisibleProduct/{id}", name="makeVisibleProduct")
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     */
    public function makeVisibleProduct(
        Product $product,
        EntityManagerInterface $entityManager)
    {
        $product->setVisibilityAdmin(1);
        $entityManager->flush();
        $this->addFlash('success', 'Product made visible!');
        return $this->redirectToRoute('listofproducts');
    }

    /**
     * @Route("/admin/makeNotVisibleProduct/{id}", name="makeNotVisibleProduct")
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     */
    public function makeNotVisibleProduct(
        Product $product,
        EntityManagerInterface $entityManager)
    {
        $product->setVisibilityAdmin(0);
        $entityManager->flush();
        $this->addFlash('success', 'Product made not visible!');
        return $this->redirectToRoute('listofproducts');
    }

    /**
     * @Route("/admin/users", name="listofusers")
     * @param UserRepository $userRepository
     * @return Response
     */
    public function listOfUsers(UserRepository $userRepository)
    {
        $listofusers = $userRepository->findBy([], [
            'lastName' => 'ASC', 'firstName' => 'ASC'
        ]);
        return $this->render('admin/viewusers.html.twig', [
            'users' => $listofusers
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
     * @Route("/admin/itemsoldperuser", name="viewpeopleitemsperperson")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function listOfPeopleThatBoughtMyProduct(
        Request $request,
        SoldRepository $soldRepository,
        ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        $soldperuser = [];
        $form = $this->createForm(ListOfUserBoughtItemsFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $userid = $form->getData()->getUser();
            /**
             * @var User $userid
             */
            $message = $userid->getFirstName() .' '. $userid->getLastName();
            /**
             * @var Product $product
             */
            $soldperuser[] = $soldRepository->findBy([
                'user' => $userid,
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $message = "All users";
            /**
             * @var Product $product
             */
            $soldperuser[] = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('admin/viewpeopleitemsperperson.html.twig', [
            'form' => $form->createView(),
            'solditems' => $soldperuser,
            'message' => $message
        ]);
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
        $listforproduct = [];
        $form = $this->createForm(AdminListOfBoughtItemsPerProductFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData()->getProduct();
            $message = $product->getName();
            $listforproduct[] = $soldRepository->findBy([
                'product' => $product->getId()
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $message = "All products";
            $listforproduct[] = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('admin/viewpeopleitemsperproduct.html.twig', [
            'form' => $form->createView(),
            'solditems' => $listforproduct,
            'message' => $message
        ]);
    }

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}