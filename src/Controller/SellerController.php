<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;
use App\Entity\Category;
use App\Entity\ProductCategory;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Product;
use App\Form\ListOfBoughtItemsPerProductFormType;
use App\Form\ListOfUserBoughtItemsFormType;
use App\Form\ProductFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductImageFormType;
use App\Form\ProductQuantityFormType;
use App\Repository\CategoryRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SoldRepository;
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
            if(empty($customUrl)) {
                $customUrl = $product->getName();
            }
            $product->setCustomUrl(str_replace(' ', '-', $customUrl));
            $product->setUser($this->getUser());
            $product->setVisibility(1);
            $product->setVisibilityAdmin(1);
            $product->setImage($fileName);
            $entityManager->persist($product);
            $entityManager->flush();
            $this->addFlash('success', 'Inserted new product!');
            return $this->redirectToRoute('insertproduct');
        }
        return $this->render('seller/newproduct.html.twig', [
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
        return $this->render('seller/showallproducts.html.twig', [
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
        $products = $productRepository->findBy([
            'user' => $this->getUser()->getId()
        ]);
        return $this->render('seller/showmyproducts.html.twig', [
            'products' => $products
        ]);
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
        if($product->getVisibility() === 0) {
            $product->setVisibility(1);
            $entityManager->flush();
            $this->addFlash('success', 'Product made visible!');
            return $this->redirectToRoute('showmyproducts');
        } elseif($product->getVisibility() === 1) {
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
        if($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('showmyproducts');
        }
        $productIm = $product->getImage();
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $customUrl = $product->getCustomUrl();
            if(empty($customUrl)) {
                $customUrl = $product->getName();
            }
            $product->setCustomUrl(str_replace(' ', '-', $customUrl));
            $product->setImage($productIm);
            $allProductsFromProductCategory = $productCategoryRepository->findBy([
                'product' => $product->getId()
            ]);
            foreach($allProductsFromProductCategory as $oneProductsFromProductCategory) {
                $entityManager->remove($oneProductsFromProductCategory);
                $entityManager->flush();
            }
            $entityManager->merge($product);
            $entityManager->flush();
            $this->addFlash('success', 'Updated the Product Info!');
            return $this->redirectToRoute('showmyproducts');
        }
        return $this->render('seller/updatemyproductinfo.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/seller/updatemyproductquantity/{id}", name="updatemyproductquantity")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function updateMyProductAvailableQuantity(
        Product $product,
        Request $request,
        EntityManagerInterface $entityManager)
    {
        if($this->getUser() !== $product->getUser()) {
            return $this->redirectToRoute('showmyproducts');
        }
        $productIm = $product->getImage();
        $product->setImage(new File($this->getParameter('image_directory').'/'.$product->getImage()));
        $form = $this->createForm(ProductQuantityFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $product->setImage($productIm);
            $entityManager->merge($product);
            $entityManager->flush();
            $this->addFlash('success', 'Updated the Product Available Quantity!');
            return $this->redirectToRoute('showmyproducts');
        }
        return $this->render('seller/updatemyproductquantity.html.twig', [
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
        if($this->getUser() !== $product->getUser()) {
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
        return $this->render('seller/updatemyproductimage.html.twig', [
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
        $soldperuser = [];
        $form = $this->createForm(ListOfUserBoughtItemsFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
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

        return $this->render('seller/peoplethatboughtmyproduct.html.twig', [
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
        if($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('peoplethatboughtmyproduct');
        }
        $sold->setConfirmed(1);
        $entityManager->flush();
        $this->addFlash('success', 'Buy confirmed!');
        return $this->redirectToRoute('peoplethatboughtmyproduct', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/unconfirmbuyperperson/{id}", name="unconfirmbuyperperson")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function unConfirmBuyPerPerson(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('peoplethatboughtmyproduct');
        }
        $sold->setConfirmed(0);
        $entityManager->flush();
        $this->addFlash('success', 'Buy unconfirmed!');
        return $this->redirectToRoute('peoplethatboughtmyproduct', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/deletesolditemperuser/{id}", name="deletesolditemperuser")
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function deleteSoldItemPerUser(
        Sold $sold,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository)
    {
        if($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('peoplethatboughtmyproduct');
        }
        if(is_object($sold)) {
            /**
             * @var Product $productold
             */
            $productold = $productRepository->findOneBy([
                'id' => $sold->getProduct()->getId()
            ]);
            $productold->setAvailableQuantity($productold->getAvailableQuantity() + $sold->getQuantity());
            $entityManager->remove($sold);
            $entityManager->flush();
        }
        $this->addFlash('success', 'Item deleted!');
        return $this->redirectToRoute('peoplethatboughtmyproduct', [
            'id' => $this->getUser()->getId()
        ]);
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
        $listforproduct = [];
        $sold = new Sold();
        $form = $this->createForm(ListOfBoughtItemsPerProductFormType::class, $sold, array("user" => $this->getUser()));
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
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
        return $this->render('seller/listofsolditemsperproduct.html.twig', [
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
        if($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('listofsolditemsperproduct');
        }
        $sold->setConfirmed(1);
        $entityManager->flush();
        $this->addFlash('success', 'Buy confirmed!');
        return $this->redirectToRoute('listofsolditemsperproduct', [
            'id' => $this->getUser()->getId()
        ]);
    }

    /**
     * @Route("/seller/unconfirmbuyperproduct/{id}", name="unconfirmbuyperproduct")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function unConfirmBuyPerProduct(
        Sold $sold,
        EntityManagerInterface $entityManager)
    {
        if($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('listofsolditemsperproduct');
        }
        $sold->setConfirmed(0);
        $entityManager->flush();
        $this->addFlash('success', 'Buy unconfirmed!');
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
        if($this->getUser() !== $sold->getProduct()->getUser()) {
            return $this->redirectToRoute('listofsolditemsperproduct');
        }
        if(is_object($sold)) {
            /**
             * @var Product $productold
             */
            $productold = $productRepository->findOneBy([
                'id' => $sold->getProduct()->getId()
            ]);
            $productold->setAvailableQuantity($productold->getAvailableQuantity() + $sold->getQuantity());
            $entityManager->remove($sold);
            $entityManager->flush();
        }
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
        return $this->render('seller/viewsolditem.html.twig', [
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