<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:11 AM
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Product;
use App\Service\ProductService;
use App\Form\AdminListOfCategoriesFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductQuantityFormType;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class ProductController extends AbstractController
{
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
     * @param                                     ProductService $productService
     * @param                                     Request $request
     * @param                                     Product $product
     * @return                                    \Symfony\Component\HttpFoundation\Response
     */
    public function updateMyProductImage(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductService $productService
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
        } elseif ($product->getVisibilityAdmin() === 1) {
            $product->setVisibilityAdmin(0);
            $this->addFlash('success', 'Product hidden!');
        } else {
            $this->addFlash('warning', 'Something went wrong');
        }

        $entityManager->flush();
        return $this->redirectToRoute('list_of_products');
    }
}
