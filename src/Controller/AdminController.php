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
use App\Entity\Sold;
use App\Form\CategoryFormType;
use App\Form\ListOfUserBoughtItemsFormType;
use App\Form\AdminListOfBoughtItemsPerProductFormType;
use App\Form\AdminListOfCategoriesFormType;
use App\Repository\CategoryRepository;
use App\Repository\SellerRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/admin/checkapplyforseller", name="checkapplyforseller")
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
     * @Route("/admin/checkoneapplierforseller/{id}", name="checkoneapplierforseller")
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


        return $this->render('admin/viewapplier.html.twig', [
            'seller' => $seller,
            'verified' => 1
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

        return $this->render('admin/viewapplier.html.twig', [
            'seller' => $seller,
            'verified' => 0
        ]);
    }

    /**
     * @Route("/admin/listofcategories", name="listofcategories")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @return Response
     */
    public function listOfAllCategories(Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
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
     * @Route("/admin/checkonecategory/{id}", name="checkonecategory")
     * @param CategoryRepository $categoryRepository
     * @param Category $category
     * @return Response
     */
    public function listOneCategory(Category $category, CategoryRepository $categoryRepository)
    {
        $cat = $categoryRepository->findOneBy(['id' => $category->getId()]);
        return $this->render('admin/viewcategory.html.twig', [
            'category' => $cat,
        ]);
    }

    /**
     * @Route("/admin/listofproducts", name="listofproducts")
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param Request $request
     * @return Response
     */
    public function showAllProducts(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $products = [];
        $form = $this->createForm(AdminListOfCategoriesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $category1 = $form->getData();
            /**
             * @var Category $category
             */
            $category = $category1->getId();
            $message = $category->getName();
            $products[] = $productRepository->findBy([
                'category' => $category
            ]);
        } else {
            $products[] = $productRepository->findBy([

            ], [
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
     * @Route("/admin/vieweachcategory/{id}", name="vieweachcategory")
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param Category $category
     * @return Response
     */
    public function showProductsPerCategory(Category $category, CategoryRepository $categoryRepository, ProductRepository $productRepository)
    {
        $products = $productRepository->findBy([
            'category' => $category->getId()
        ]);
        return $this->render('admin/viewcategoryproducts.html.twig', [
            'products' => $products,
            'categoryName' => $category->getName()
        ]);
    }

    /**
     * @Route("/admin/viewpeopleitemsperperson", name="viewpeopleitemsperperson")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function listOfPeopleThatBoughtMyProduct(Request $request, SoldRepository $soldRepository, ProductRepository $productRepository)
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
     * @Route("/admin/viewpeopleitemsperproduct", name="viewpeopleitemsperproduct")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function listOfBoughtItemsPerProduct(Request $request, SoldRepository $soldRepository, ProductRepository $productRepository)
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

    /**
     * @Route("/admin/makeVisible/{id}", name="makeVisible")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @param Category $category1
     * @return Response
     */
    public function makeVisible(Category $category1, Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $category1->setVisibilityAdmin(1);
        $entityManager->flush();
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
     * @Route("/admin/makeNotVisible/{id}", name="makeNotVisible")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param CategoryRepository $categoryRepository
     * @param Category $category1
     * @return Response
     */
    public function makeNotVisible(Category $category1, Request $request, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository)
    {
        $category1->setVisibilityAdmin(0);
        $entityManager->flush();
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
     * @Route("/admin/makeVisibleProduct/{id}", name="makeVisibleProduct")
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $product1
     * @return Response
     */
    public function makeVisibleProduct(Product $product1, EntityManagerInterface $entityManager, Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $product1->setVisibilityAdmin(1);
        $entityManager->flush();
        $products = [];
        $form = $this->createForm(AdminListOfCategoriesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $category1 = $form->getData();
            /**
             * @var Category $category
             */
            $category = $category1->getId();
            $message = $category->getName();
            $products[] = $productRepository->findBy([
                'category' => $category
            ]);
        } else {
            $products[] = $productRepository->findBy([

            ], [
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
     * @Route("/admin/makeNotVisibleProduct/{id}", name="makeNotVisibleProduct")
     * @param ProductRepository $productRepository
     * @param CategoryRepository $categoryRepository
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $product1
     * @return Response
     */
    public function makeNotVisibleProduct(Product $product1, Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $product1->setVisibilityAdmin(0);
        $entityManager->flush();
        $products = [];
        $form = $this->createForm(AdminListOfCategoriesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $category1 = $form->getData();
            /**
             * @var Category $category
             */
            $category = $category1->getId();
            $message = $category->getName();
            $products[] = $productRepository->findBy([
                'category' => $category
            ]);
        } else {
            $products[] = $productRepository->findBy([

            ], [
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
}