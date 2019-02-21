<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 08:38
 */

namespace App\Controller;
use App\Entity\Category;
use App\Entity\Product;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Seller;
use App\Form\SellerFormType;
use App\Form\SoldFormType;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SellerRepository;
use App\Repository\SoldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdvertisementController extends AbstractController
{
    /**
     * @Route("/", name="advertisement_index")
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function index(CategoryRepository $categoryRepository, ProductRepository $productRepository)
    {
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findBy(
            array('visibility' => 1),
            array('id' => 'DESC'),
            10,
            0
        );
        return $this->render('advertisement/index.html.twig', [
            'categories' => $categories,
            'products' => $products
        ]);
    }

    /**
     * @Route("/showcategories/{id}", name="showcategories")
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param Category $category
     * @return Response
     */
    public function showProductsPerCategory(Category $category, CategoryRepository $categoryRepository, ProductRepository $productRepository)
    {
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findBy([
            'category' => $category->getId()
        ]);
        return $this->render('advertisement/categoryproducts.html.twig', [
            'categories' => $categories,
            'products' => $products
        ]);
    }

    /**
     * @Route("/applyforseller", name="applyforseller_index")
     * @return Response
     * @param EntityManagerInterface $entityManager
     * @param SellerRepository $sellerRepository
     * @param Request $request
     */
    public function applyForSeller(Request $request, EntityManagerInterface $entityManager, SellerRepository $sellerRepository)
    {
        $applied = $sellerRepository->findOneBy([
            'user' => $this->getUser()
        ]);

        if($applied !== null) {
            return $this->render('advertisement/applyforseller.html.twig', [
                'message' => 'Applied',
                'applied' => $applied
            ]);
        } else {
            $form = $this->createForm(SellerFormType::class);
            $form->handleRequest($request);
            if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
                /** @var Seller $seller */
                $seller = $form->getData();
                $seller->setUser($this->getUser());
                $seller->setVerified(0);
                $entityManager->persist($seller);
                $entityManager->flush();
                $this->addFlash('success', 'Applied for seller position!');
                return $this->redirectToRoute('advertisement_index');
            }
            return $this->render('advertisement/applyforseller.html.twig', [
                'form' => $form->createView(),
                'message' => ''
            ]);
        }
    }

    /**
     * @Route("/checkproduct/{id}", name="checkproduct")
     * @param CategoryRepository $categoryRepository
     * @param SellerRepository $sellerRepository
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @param Request $request
     * @return Response
     */
    public function checkProduct(Request $request, Product $product, EntityManagerInterface $entityManager, CategoryRepository $categoryRepository, SellerRepository $sellerRepository)
    {
        $categories = $categoryRepository->findAll();
        $seller = $sellerRepository->findOneBy([
            'user' => $product->getSeller()
        ]);
        $form = $this->createForm(SoldFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /** @var Sold $sold */
            $sold = $form->getData();
            $sold->setUser($this->getUser());
            $sold->setProduct($product);
            $entityManager->persist($sold);
            $entityManager->flush();
            $this->addFlash('success', 'Applied for seller position!');
            return $this->redirectToRoute('checkproduct', ['id' => $product->getId()]);
        }
        return $this->render('advertisement/productpage.html.twig', [
            'categories' => $categories,
            'product' => $product,
            'seller' => $seller,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/myitems", name="myitems")
     * @param CategoryRepository $categoryRepository
     * @param SoldRepository $soldRepository
     * @return Response
     */
    public function myItems(CategoryRepository $categoryRepository, SoldRepository $soldRepository)
    {
        $categories = $categoryRepository->findAll();
        $myitems = $soldRepository->findBy([
            'user' => $this->getUser()->getId()
        ]);

        return $this->render('advertisement/myitems.html.twig', [
            'categories' => $categories,
            'myitems' => $myitems
        ]);
    }
}