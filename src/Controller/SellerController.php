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
use App\Entity\Seller;
use App\Form\ListOfBoughtItemsPerProductFormType;
use App\Form\ListOfUserBoughtItemsFormType;
use App\Form\ProductFormType;
use App\Form\ProductInfoFormType;
use App\Form\ProductImageFormType;
use App\Repository\ProductRepository;
use App\Repository\SellerRepository;
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
     * @Route("/seller/insertproduct", name="insertproduct")
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
     * @Route("/seller/showproducts", name="showproducts")
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function showAllProducts(ProductRepository $productRepository)
    {
        $products = $productRepository->findAll();
        return $this->render('seller/showallproducts.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/seller/showmyproducts", name="showmyproducts")
     * @param ProductRepository $productRepository
     * @param SellerRepository $sellerRepository
     * @return Response
     */
    public function showMyProducts(ProductRepository $productRepository, SellerRepository $sellerRepository)
    {
        $seller = $sellerRepository->findOneBy([
            "user" => $this->getUser()->getId()
        ]);

        $products = $productRepository->findBy([
            'user' => $seller->getUser()->getId()
            ]);

        return $this->render('seller/showmyproducts.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/seller/makeproductvisible/{id}", name="makeproductvisible")
     * @param ProductRepository $productRepository
     * @param SellerRepository $sellerRepository
     * @param EntityManagerInterface $entityManager
     * @param Product $product1
     * @return Response
     */
    public function makeProductVisible(Product $product1, EntityManagerInterface $entityManager, ProductRepository $productRepository, SellerRepository $sellerRepository)
    {
        $product1->setVisibility(1);
        $entityManager->flush();
        $seller = $sellerRepository->findOneBy([
            "user" => $this->getUser()->getId()
        ]);

        $products = $productRepository->findBy([
            'user' => $seller->getUser()->getId()
        ]);

        return $this->render('seller/showmyproducts.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/seller/makeproductnotvisible/{id}", name="makeproductnotvisible")
     * @param ProductRepository $productRepository
     * @param SellerRepository $sellerRepository
     * @param EntityManagerInterface $entityManager
     * @param Product $product1
     * @return Response
     */
    public function makeProductNotVisible(Product $product1, EntityManagerInterface $entityManager, ProductRepository $productRepository, SellerRepository $sellerRepository)
    {
        $product1->setVisibility(0);
        $entityManager->flush();
        $seller = $sellerRepository->findOneBy([
            "user" => $this->getUser()->getId()
        ]);

        $products = $productRepository->findBy([
            'user' => $seller->getUser()->getId()
        ]);

        return $this->render('seller/showmyproducts.html.twig', [
            'products' => $products
        ]);
    }

    /**
     * @Route("/seller/updatemyproductinfo/{id}", name="updatemyproductinfo")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $productOld
     * @return Response
     */
    public function updateMyProductInfo(Product $productOld, Request $request, EntityManagerInterface $entityManager)
    {
        $product = new Product();
        $product->setName($productOld->getName());
        $product->setPrice($productOld->getPrice());
        $product->setUser($this->getUser());
        $product->setAvailableQuantity($productOld->getAvailableQuantity());
        $product->setCategory($productOld->getCategory());
        $product->setContent($productOld->getContent());
        $product->setImage(new File($this->getParameter('image_directory').'/'.$productOld->getImage()));

        $form = $this->createForm(ProductInfoFormType::class, $product);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_SELLER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Product $product
             */
            $product = $form->getData();
            $product->setUser($this->getUser());
            $product->setVisibility($productOld->getVisibility());
            $product->setVisibilityAdmin($productOld->getVisibilityAdmin());
            $product->setId($productOld->getId());
            $product->setImage($productOld->getImage());
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
     * @Route("/seller/updatemyproductimage/{id}", name="updatemyproductimage")
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Product $productOld
     * @return Response
     */
    public function updateMyProductImage(Product $productOld, Request $request, EntityManagerInterface $entityManager)
    {
        $product1 = new Product();
        $product1->setName($productOld->getName());
        $product1->setPrice($productOld->getPrice());
        $product1->setImage(new File($this->getParameter('image_directory').'/'.$productOld->getImage()));
        $form = $this->createForm(ProductImageFormType::class, $product1);
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
            $product->setId($productOld->getId());
            $product->setName($productOld->getName());
            $product->setPrice($productOld->getPrice());
            $product->setUser($this->getUser());
            $product->setAvailableQuantity($productOld->getAvailableQuantity());
            $product->setCategory($productOld->getCategory());
            $product->setContent($productOld->getContent());
            $product->setVisibility($productOld->getVisibility());
            $product->setVisibilityAdmin($productOld->getVisibility());
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
     * @Route("/seller/peoplethatboughtmyproduct", name="peoplethatboughtmyproduct")
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
     * @Route("/seller/listofsolditemsperproduct", name="listofsolditemsperproduct")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function listOfBoughtItemsPerProduct(Request $request, SoldRepository $soldRepository, ProductRepository $productRepository)
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
     * @Route("/seller/confirmbuy/{id}", name="confirmbuy")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold1
     * @return Response
     */
    public function confirmBuy(Sold $sold1, Request $request, EntityManagerInterface $entityManager, SoldRepository $soldRepository, ProductRepository $productRepository)
    {
        $sold1->setConfirmed(1);
        $entityManager->flush();
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
     * @Route("/seller/unconfirmbuy/{id}", name="unconfirmbuy")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold1
     * @return Response
     */
    public function unConfirmBuy(Sold $sold1, Request $request, EntityManagerInterface $entityManager, SoldRepository $soldRepository, ProductRepository $productRepository)
    {
        $sold1->setConfirmed(0);
        $entityManager->flush();
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
     * @Route("/seller/deletesolditemperuser/{id}", name="deletesolditemperuser")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold1
     * @param Product $productold
     * @return Response
     */
    public function deleteSoldItemPerUser(Sold $sold1, Request $request, EntityManagerInterface $entityManager, SoldRepository $soldRepository, ProductRepository $productRepository)
    {
        if(is_object($sold1)) {
            /**
             * @var Product $productold
             */
            $productold = $productRepository->findOneBy([
                'id' => $sold1->getProduct()->getId()
            ]);
            $productold->setAvailableQuantity($productold->getAvailableQuantity() + $sold1->getQuantity());

            $entityManager->remove($sold1);
            $entityManager->flush();
        }

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
     * @Route("/seller/confirmbuyperproduct/{id}", name="confirmbuyperproduct")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold1
     * @return Response
     */
    public function confirmBuyPerProduct(Sold $sold1, Request $request, EntityManagerInterface $entityManager, SoldRepository $soldRepository, ProductRepository $productRepository)
    {
        $sold1->setConfirmed(1);
        $entityManager->flush();
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
     * @Route("/seller/unconfirmbuyperproduct/{id}", name="unconfirmbuyperproduct")
     * @param Request $request
     * @param SoldRepository $soldRepository
     * @param ProductRepository $productRepository
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold1
     * @return Response
     */
    public function unConfirmBuyPerProduct(Sold $sold1, Request $request, EntityManagerInterface $entityManager, SoldRepository $soldRepository, ProductRepository $productRepository)
    {
        $sold1->setConfirmed(0);
        $entityManager->flush();
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

    private function generateUniqueFileName()
    {
        // md5() reduces the similarity of the file names generated by
        // uniqid(), which is based on timestamps
        return md5(uniqid());
    }
}