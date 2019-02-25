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
use App\Entity\Seller;
use App\Entity\Comment;
use App\Form\CommentFormType;
use App\Form\ContactFormType;
use App\Form\SellerFormType;
use App\Form\SoldFormType;
use App\Repository\CategoryRepository;
use App\Repository\SellerRepository;
use App\Repository\SoldRepository;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Handler\SwiftMailerHandler;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\ProductService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class AdvertisementController extends AbstractController
{
    /**
     * @Route("/", name="advertisement_index")
     * @param CategoryRepository $categoryRepository
     * @param ProductService $productService
     * @param Request $request
     * @return Response
     */
    public function index(
        CategoryRepository $categoryRepository,
        ProductService $productService,
        Request $request
    )
    {
        $categories = $categoryRepository->findBy([
            'visibilityAdmin' => 1
        ]);
        $data = $productService->returnData($request);
        return $this->render('advertisement/index.html.twig', [
            'categories' => $categories,
            'products' => $data
        ]);
    }

    /**
     * @Route("/showcategories/{id}", name="showcategories")
     * @param CategoryRepository $categoryRepository
     * @param ProductService $productService
     * @param Request $request
     * @param Category $category
     * @return Response
     */
    public function showProductsPerCategory(
        Category $category,
        CategoryRepository $categoryRepository,
        ProductService $productService,
        Request $request)
    {
        $categories = $categoryRepository->findBy([
            'visibilityAdmin' => 1
        ]);
        $data = $productService->returnDataPerCategory($request, $category);
        return $this->render('advertisement/categoryproducts.html.twig', [
            'categories' => $categories,
            'products' => $data
        ]);
    }

    /**
     * @Route("/applyforseller", name="applyforseller_index")
     * @return Response
     * @param EntityManagerInterface $entityManager
     * @param SellerRepository $sellerRepository
     * @param Request $request
     */
    public function applyForSeller(
        Request $request,
        EntityManagerInterface $entityManager,
        SellerRepository $sellerRepository)
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
    public function checkProduct(
        Request $request,
        Product $product,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        SellerRepository $sellerRepository)
    {
        $categories = $categoryRepository->findBy([
            'visibilityAdmin' => 1
        ]);
        $seller = $sellerRepository->findOneBy([
            'user' => $product->getUser()
        ]);
        $form = $this->createForm(SoldFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /** @var Sold $sold */
            $sold = $form->getData();
            $quan = $sold->getQuantity();
            if($quan === null ) {
                $this->addFlash('warning', 'Please insert correct number in field Quantity!');
                return $this->redirectToRoute('checkproduct', ['id' => $product->getId()]);
            }
            if($sold->getQuantity() > $product->getAvailableQuantity()) {
                $this->addFlash('warning', 'Not enough available quantity!');
                return $this->redirectToRoute('checkproduct', ['id' => $product->getId()]);
            } else {
                $sold->setUser($this->getUser());
                $sold->setProduct($product);
                $sold->setPrice($product->getPrice());
                $sold->setTotalPrice($sold->getQuantity() * $sold->getPrice());
                $sold->setConfirmed(0);
                $product->setAvailableQuantity($product->getAvailableQuantity() - $sold->getQuantity());
                $entityManager->persist($sold);
                $entityManager->flush();
                $this->addFlash('success', 'Bought the product!');
                return $this->redirectToRoute('checkproduct', ['id' => $product->getId()]);
            }
        }

        $formMail = $this->createForm(ContactFormType::class);
        $formMail->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formMail->isSubmitted() && $formMail->isValid()) {
            $formMailData = $formMail->getData();
            $name = $formMailData['name'];
            $from = $formMailData['from'];
            $message = $formMailData['message'];
            $to = $product->getUser()->getEmail();
            if(empty($name) || empty($from) || empty($message)) {
                $this->addFlash('warning', 'Please fill out all fields to send mail!');
                return $this->redirectToRoute('checkproduct', [
                    'id' => $product->getId()
                ]);
            } elseif(filter_var($from, FILTER_VALIDATE_EMAIL)){
                $this->addFlash('success', 'Mail sent. Name: ' . $name . ', from: ' . $from . ', to: '
                    . $to . ', message: ' . $message);
                return $this->redirectToRoute('checkproduct', [
                    'id' => $product->getId()
                ]);
            } else {
                $this->addFlash('warning', 'Your email is not valid!');
                return $this->redirectToRoute('checkproduct', [
                    'id' => $product->getId()
                ]);
            }
        }

        $formComment = $this->createForm(CommentFormType::class);
        $formComment->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formComment->isSubmitted() && $formComment->isValid()) {
            /** @var Comment $comment */
            $comment = $formComment->getData();
            $comment->setUser($this->getUser());
            $comment->setProduct($product);
            $product->addComment($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment added!');
            return $this->redirectToRoute('checkproduct', [
                'id' => $product->getId()
            ]);
        }

        return $this->render('advertisement/productpage.html.twig', [
            'categories' => $categories,
            'product' => $product,
            'seller' => $seller,
            'form' => $form->createView(),
            'commentForm' => $formComment->createView(),
            'emailForm' => $formMail->createView()
        ]);
    }

    /**
     * @Route("/myitems", name="myitems")
     * @param CategoryRepository $categoryRepository
     * @param ProductService $productService
     * @param Request $request
     * @return Response
     */
    public function myItems(
        CategoryRepository $categoryRepository,
        ProductService $productService,
        Request $request)
    {
        $categories = $categoryRepository->findBy([
            'visibilityAdmin' => 1
        ]);
        $data = $productService->returnDataMyItems($request, $this->getUser()->getId());
        return $this->render('advertisement/myitems.html.twig', [
            'categories' => $categories,
            'myitems' => $data
        ]);
    }

    /**
     * @Route("/exceluser", name="exceluser")
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @param SoldRepository $soldRepository
     */
    public function excelUser(SoldRepository $soldRepository)
    {
        $spreadsheet = new Spreadsheet();
        $sold = $soldRepository->findBy(['user' => $this->getUser()], ['product' => 'ASC']);

        /* @var $sheet \PhpOffice\PhpSpreadsheet\Writer\Xlsx\Worksheet */
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Product name');
        $sheet->setCellValue('B1', 'Category name');
        $sheet->setCellValue('C1', 'Seller');
        $sheet->setCellValue('D1', 'Email');
        $sheet->setCellValue('E1', 'Qauntity');
        $sheet->setCellValue('F1', 'Price');
        $sheet->setCellValue('G1', 'Total Price');
        $sheet->setCellValue('H1', 'Bought at');
        $i = 2;
        foreach($sold as $item) {
            /**
             * @var Sold $item
             */
            $sheet->setCellValue('A' . $i, $item->getProduct()->getName());
            $sheet->setCellValue('B' . $i, $item->getProduct()->getCategory()->getName());
            $sheet->setCellValue('C' . $i, $item->getProduct()->getUser()->getFullName());
            $sheet->setCellValue('D' . $i, $item->getProduct()->getUser()->getEmail());
            $sheet->setCellValue('E' . $i, $item->getQuantity());
            $sheet->setCellValue('F' . $i, $item->getPrice());
            $sheet->setCellValue('G' . $i, $item->getTotalPrice());
            $sheet->setCellValue('H' . $i, $item->getBoughtAt());
            $i++;
        }

        $sheet->setTitle("Bought items");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'my_first_excel_symfony4.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}