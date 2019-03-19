<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\Wishlist;
use App\Form\ListOfBoughtItemsPerProductFormType;
use App\Form\ListOfUserBoughtItemsFormType;
use App\Form\ProductFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductQuantityFormType;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
use App\Repository\WishlistRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\File;

class SellerController extends AbstractController
{
    /**
     * @Route("/seller/", name="seller_index")
     * @return Response
     */
    public function index()
    {
        return $this->render('seller/index.html.twig', [
        ]);
    }

    /**
     * @Route("/seller/new-product", name="insert_product")
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function newProduct(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(ProductFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
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
            $customUrl = $product->getCustomUrl();
            if (empty($customUrl)) {
                $customUrl = $product->getName();
            }
            $productUrlNum = '-' . rand(10000000,99999999);
            $pageName = $customUrl . $productUrlNum;
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
        return $this->render('/seller/new_product.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/all-products", name="show_products")
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function showAllProducts(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy([],[
            'name' => 'ASC'
        ]);
        return $this->render('/seller/show_all_products.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/seller/my-products", name="show_my_products")
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function showMyProducts(ProductRepository $productRepository)
    {
        $products = $this->getMyProducts($productRepository);
        return $this->render('/seller/show_my_products.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @param ProductRepository $productRepository
     * @return array
     */
    public function getMyProducts(ProductRepository $productRepository)
    {
        $products = $productRepository->findBy([
            'user' => $this->getUser()->getId()
        ]);

        return $products;
    }

    /**
     * @Route("/seller/product-visibility/{id}", name="update_product_visibility_seller")
     * @param EntityManagerInterface $entityManager
     * @param Product $product
     * @return Response
     */
    public function updateProductVisibilitySeller(
        Product $product,
        EntityManagerInterface $entityManager)
    {
        if ($product->getVisibility() === 0) {
            $product->setVisibility(1);
            $entityManager->flush();
            $this->addFlash('success', 'Product made visible!');
            return $this->redirectToRoute('show_my_products');
        } elseif ($product->getVisibility() === 1) {
            $product->setVisibility(0);
            $entityManager->flush();
            $this->addFlash('success', 'Product hidden!');
            return $this->redirectToRoute('show_my_products');
        } else {
            $this->addFlash('warning', 'Something went wrong');
            return $this->redirectToRoute('show_my_products');
        }
    }

    /**
     * @Route("/seller/update-product-info/{id}", name="update_my_product_info")
     * @param EntityManagerInterface $entityManager
     * @param ProductCategoryRepository $productCategoryRepository
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateMyProductInfo(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        ProductCategoryRepository $productCategoryRepository)
    {
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }
        $productIm = $product->getImage();
        $productUrlNum = substr($product->getCustomUrl(), -9);
        $productUrl = substr($product->getCustomUrl(), 0, -9);
        $product->setCustomUrl($productUrl);
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $customUrl = $product->getCustomUrl();
            if (empty($customUrl)) {
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
            return $this->redirectToRoute('show_my_products');
        }
        return $this->render('/seller/update_my_product_info.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/update-product-quantity/{id}", name="update_my_product_quantity")
     * @param EntityManagerInterface $entityManager
     * @param WishlistRepository $wishlistRepository
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateMyProductAvailableQuantity(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository)
    {
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }
        $productIm = $product->getImage();
        $productBeforeQuantity = $product->getAvailableQuantity();
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductQuantityFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
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
            $this->addFlash('success', 'Updated the Product Available Quantity!');
            return $this->redirectToRoute('show_my_products');
        }
        return $this->render('/seller/update_my_product_quantity.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/update-product-image/{id}", name="update_my_product_image")
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
        if ($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('show_my_products');
        }
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductImageFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
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
            $this->addFlash('success', 'Updated the Product Image!');
            return $this->redirectToRoute('show_my_products');
        }
        return $this->render('/seller/update_my_product_image.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/sold-items-per-user", name="sold_items_per_user")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function listOfPeopleThatBoughtMyProduct(Request $request, SoldRepository $soldRepository, ProductRepository $productRepository)
    {
        $products = $productRepository->findBy([
            'user' => $this->getUser()->getId()
        ]);
        $form = $this->createForm(ListOfUserBoughtItemsFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            $userid = $form->getData()->getUser();
            /**
             * @var User $userid
             */
            $message = $userid->getFullName();
            /**
             * @var Product $product
             */
            $soldperuser = $soldRepository->findBy([
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
            $soldperuser = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }

        return $this->render('/seller/list_of_sold_items_per_user.html.twig', [
            'form' => $form->createView(),
            'solditems' => $soldperuser,
            'message' => $message
        ]);
    }


    /**
     * @Route("/seller/confirm-buy-per-person/{id}", name="confirm_buy_per_person")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function confirmBuyPerPerson(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('sold_items_per_user');
        }

        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $entityManager->flush();
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $entityManager->flush();
            $this->addFlash('success', 'Buy unconfirmed!');
        }
        return $this->redirectToRoute('sold_items_per_user');
    }

    /**
     * @Route("/seller/delete-sold-item-per-user/{id}", name="delete_sold_item_per_user")
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
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('sold_items_per_user');
        }

        /**
         * @var Product $productold
         */
        $productold = $productRepository->findOneBy([
            'id' => $sold->getProduct()->getId()
        ]);
        if ($productold->getAvailableQuantity() === 0) {
            $wishlistProducts = $wishlistRepository->findBy([
                'product' => $productold->getId()]);
            foreach ($wishlistProducts as $wishlistProduct) {
                /**
                 * @var $wishlistProduct Wishlist
                 */
                $wishlistProduct->setNotify(1);
                $entityManager->persist($wishlistProduct);
            }
        }
        $productold->setAvailableQuantity($productold->getAvailableQuantity() + $sold->getQuantity());
        $entityManager->remove($sold);
        $entityManager->flush();

        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute('sold_items_per_user');
    }

    /**
     * @Route("/seller/sold-items-per-product", name="list_of_sold_items_per_product")
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
        $products = $productRepository->findBy([
            'user' => $this->getUser()->getId()
        ]);
        $sold = new Sold();
        $form = $this->createForm(ListOfBoughtItemsPerProductFormType::class, $sold, array("user" => $this->getUser()));
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData()->getProduct();
            $message = $product->getName();
            $listforproduct = $soldRepository->findBy([
                'product' => $product->getId()
            ], [
                'boughtAt' => 'DESC'
            ]);
        } else {
            $message = "All products";
            $listforproduct = $soldRepository->findBy([
                'product' => $products
            ], [
                'boughtAt' => 'DESC'
            ]);
        }
        return $this->render('/seller/list_of_sold_items_per_product.html.twig', [
            'form' => $form->createView(),
            'solditems' => $listforproduct,
            'message' => $message
        ]);
    }

    /**
     * @Route("/seller/confirm-buy-per-product/{id}", name="confirm_buy_per_product")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function confirmBuyPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('list_of_sold_items_per_product');
        }

        if ($sold->getConfirmed() === 0) {
            $sold->setConfirmed(1);
            $entityManager->flush();
            $this->addFlash('success', 'Buy confirmed!');
        } elseif ($sold->getConfirmed() === 1) {
            $sold->setConfirmed(0);
            $entityManager->flush();
            $this->addFlash('success', 'Buy unconfirmed!');
        }
        return $this->redirectToRoute('list_of_sold_items_per_product', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/delete-sold-item-per-product/{id}", name="delete_sold_item_per_product")
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function deleteSoldItemPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository)
    {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('list_of_sold_items_per_product');
        }

        /**
         * @var Product $productOld
         */
        $productOld = $productRepository->findOneBy([
            'id' => $sold->getProduct()->getId()
        ]);
        $productOld->setAvailableQuantity($productOld->getAvailableQuantity() + $sold->getQuantity());
        $entityManager->remove($sold);
        $entityManager->flush();

        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute('list_of_sold_items_per_product', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/sold-product/{id}", name="view_sold_product_info")
     * @param Sold $sold
     * @return Response
     */
    public function viewSoldProductInfo(Sold $sold)
    {
        return $this->render('/seller/view_sold_item.html.twig', [
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