<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductQuantityFormType;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;
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
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }

        if ($product->getVisibility() === false) {
            $product->setVisibility(true);
            $this->addFlash('success', 'Product made visible!');
        } elseif ($product->getVisibility() === true) {
            $product->setVisibility(false);
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
     * @param                                      Filesystem $filesystem
     * @param                                      Request $request
     * @param                                      Product $product
     * @return                                     \Symfony\Component\HttpFoundation\Response
     */
    public function updateMyProductImage(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductService $productService,
        Filesystem $filesystem
    ) {
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }

        $oldImage = $this->getParameter('image_directory') . DIRECTORY_SEPARATOR . $product->getImage();
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
                $filesystem->remove($oldImage);
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
}
