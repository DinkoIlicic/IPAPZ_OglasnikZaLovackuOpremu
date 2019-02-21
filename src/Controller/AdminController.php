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
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use App\Repository\SellerRepository;
use App\Repository\ProductRepository;
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
     * @param SellerRepository $sellerRepository
     * @param UserRepository $userRepository
     * @param Seller $seller
     * @return Response
     */
    public function verifyApplier(Seller $seller, SellerRepository $sellerRepository, UserRepository $userRepository)
    {
        $sellerRepository->updateSellerToVerify($seller->getUser()->getId());
        $userRepository->updateUserRoleSeller($seller->getUser()->getId());

        $sell = $sellerRepository->findOneBy(['id' => $seller->getId()]);

        return $this->render('admin/viewapplier.html.twig', [
            'seller' => $sell,
            'verified' => 1
        ]);
    }

    /**
     * @Route("/admin/unverifyapplier/{id}", name="unverifyapplier")
     * @param SellerRepository $sellerRepository
     * @param UserRepository $userRepository
     * @param Seller $seller
     * @return Response
     */
    public function unVerifyApplier(Seller $seller, SellerRepository $sellerRepository, UserRepository $userRepository)
    {
        $sellerRepository->updateSellerToUnverify($seller->getUser()->getId());
        $userRepository->updateUserRoleUser($seller->getUser()->getId());

        $sell = $sellerRepository->findOneBy(['id' => $seller->getId()]);

        return $this->render('admin/viewapplier.html.twig', [
            'seller' => $sell,
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
     * @return Response
     */
    public function showAllProducts(ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findAll();
        return $this->render('admin/viewproducts.html.twig', [
            'products' => $products,
            'categories' => $categories
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
}