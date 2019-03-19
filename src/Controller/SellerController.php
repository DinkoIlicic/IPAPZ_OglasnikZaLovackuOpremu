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
     * @Route("/seller/newproduct", name="insertproduct")
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
            return $this->redirectToRoute('insertproduct');
        }
        return $this->render('/seller/new_product.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/allproducts", name="showproducts")
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
     * @Route("/seller/myproducts", name="showmyproducts")
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
     * @Route("/seller/productvisibility/{id}", name="updateproductvisibilityseller")
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
            return $this->redirectToRoute('showmyproducts');
        } elseif ($product->getVisibility() === 1) {
            $product->setVisibility(0);
            $entityManager->flush();
            $this->addFlash('success', 'Product hidden!');
            return $this->redirectToRoute('showmyproducts');
        } else {
            $this->addFlash('warning', 'Something went wrong');
            return $this->redirectToRoute('showmyproducts');
        }
    }

    /**
     * @Route("/seller/updatemyproductinfo/{id}", name="updatemyproductinfo")
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
            return $this->redirectToRoute('showmyproducts');
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
            return $this->redirectToRoute('showmyproducts');
        }
        return $this->render('/seller/update_my_product_info.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/updatemyproductquantity/{id}", name="updatemyproductquantity")
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
            return $this->redirectToRoute('showmyproducts');
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
            return $this->redirectToRoute('showmyproducts');
        }
        return $this->render('/seller/update_my_product_quantity.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/updatemyproductimage/{id}", name="updatemyproductimage")
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
            return $this->redirectToRoute('showmyproducts');
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
            return $this->redirectToRoute('showmyproducts');
        }
        return $this->render('/seller/update_my_product_image.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/solditemsperuser", name="peoplethatboughtmyproduct")
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

        return $this->render('/seller/people_that_bought_my_product.html.twig', [
            'form' => $form->createView(),
            'solditems' => $soldperuser,
            'message' => $message
        ]);
    }


    /**
     * @Route("/seller/confirmbuyperperson/{id}", name="confirmbuyperperson")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function confirmBuyPerPerson(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('peoplethatboughtmyproduct');
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
        return $this->redirectToRoute('peoplethatboughtmyproduct');
    }

    /**
     * @Route("/seller/deletesolditemperuser/{id}", name="deletesolditemperuser")
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
            return $this->redirectToRoute('peoplethatboughtmyproduct');
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
        return $this->redirectToRoute('peoplethatboughtmyproduct');
    }

    /**
     * @Route("/seller/solditemsperproduct", name="listofsolditemsperproduct")
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
     * @Route("/seller/confirmbuyperproduct/{id}", name="confirmbuyperproduct")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function confirmBuyPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if ($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('listofsolditemsperproduct');
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
        return $this->redirectToRoute('listofsolditemsperproduct', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/deletesolditemperproduct/{id}", name="deletesolditemperproduct")
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
            return $this->redirectToRoute('listofsolditemsperproduct');
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
        return $this->redirectToRoute('listofsolditemsperproduct', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/soldproduct/{id}", name="viewsoldproductinfo")
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