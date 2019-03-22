<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 08:38
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Comment;
use App\Entity\CouponCodes;
use App\Entity\Product;
use App\Entity\Seller;
use App\Entity\Sold;
use App\Entity\Wishlist;
use App\Form\CommentFormType;
use App\Form\ContactFormType;
use App\Form\SellerFormType;
use App\Form\SoldFormType;
use App\Repository\CategoryRepository;
use App\Repository\CouponCodesRepository;
use App\Repository\CustomPageRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SellerRepository;
use App\Repository\SoldRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class AdvertisementController extends AbstractController
{
    /**
     * @Route("/", name="advertisement_index")
     * @param      CategoryRepository $categoryRepository
     * @param      ProductService $productService
     * @param      Request $request
     * @param      CustomPageRepository $customPageRepository
     * @return     Response
     */
    public function index(
        CategoryRepository $categoryRepository,
        ProductService $productService,
        Request $request,
        CustomPageRepository $customPageRepository
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $data = $productService->returnAllProducts($request);
        return $this->render(
            'advertisement/index.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'products' => $data
            ]
        );
    }

    /**
     * @param CategoryRepository $categoryRepository
     * @param CustomPageRepository $customPageRepository
     * @return array
     */
    public function findDataForHeader(
        CustomPageRepository $customPageRepository,
        CategoryRepository $categoryRepository
    ) {
        $allCustomPages = $customPageRepository->findAll();
        $categories = $this->getAllVisibleCategories($categoryRepository);
        $arrayWithHeaderData = [];
        $arrayWithHeaderData['customPages'] = $allCustomPages;
        $arrayWithHeaderData['categories'] = $categories;
        return $arrayWithHeaderData;
    }

    /**
     * @param  CategoryRepository $categoryRepository
     * @return array
     */
    public function getAllVisibleCategories(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findBy(
            [
                'visibilityAdmin' => 1
            ]
        );
        return $categories;
    }

    /**
     * @Route("/show-categories/{id}", name="show_categories")
     * @param                          CategoryRepository $categoryRepository
     * @param                          ProductService $productService
     * @param                          Request $request
     * @param                          Category $category
     * @param                          CustomPageRepository $customPageRepository
     * @return                         Response
     */
    public function showProductsPerCategory(
        Category $category,
        CategoryRepository $categoryRepository,
        ProductService $productService,
        Request $request,
        CustomPageRepository $customPageRepository
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $data = $productService->returnDataPerCategory($request, $category);
        return $this->render(
            '/advertisement/category_products.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'products' => $data,
            ]
        );
    }

    /**
     * @Route("/apply-for-seller", name="apply_for_seller")
     * @return                     Response
     * @param                      EntityManagerInterface $entityManager
     * @param                      SellerRepository $sellerRepository
     * @param                      CategoryRepository $categoryRepository
     * @param                      CustomPageRepository $customPageRepository
     * @param                      Request $request
     */
    public function applyForSeller(
        Request $request,
        EntityManagerInterface $entityManager,
        SellerRepository $sellerRepository,
        CustomPageRepository $customPageRepository,
        CategoryRepository $categoryRepository
    ) {

        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $applied = $sellerRepository->findOneBy(
            [
                'user' => $this->getUser()
            ]
        );
        if ($applied !== null) {
            return $this->render(
                '/advertisement/apply_for_seller.html.twig',
                [
                    'message' => 'Applied',
                    'applied' => $applied,
                    'pages' => $arrayWithHeaderData['customPages'],
                    'categories' => $arrayWithHeaderData['categories'],
                ]
            );
        };

        $form = $this->createForm(SellerFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Seller $seller
             */
            $seller = $form->getData();
            $seller->setUser($this->getUser());
            $seller->setVerified(0);
            $entityManager->persist($seller);
            $entityManager->flush();
            $this->addFlash('success', 'Applied for seller position!');
            return $this->redirectToRoute('advertisement_index');
        }
        return $this->render(
            '/advertisement/apply_for_seller.html.twig',
            [
                'form' => $form->createView(),
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'message' => ''
            ]
        );
    }

    /**
     * @Route("/check-product/{pageName}", name="check_product")
     * @param                              CategoryRepository $categoryRepository
     * @param                              EntityManagerInterface $entityManager
     * @param                              WishlistRepository $wishlistRepository
     * @param                              ProductRepository $productRepository
     * @param                              CustomPageRepository $customPageRepository
     * @param                              CouponCodesRepository $couponCodesRepository
     * @param                              ProductCategoryRepository $productCategoryRepository
     * @param                              Request $request
     * @return                             Response
     * @var                                $pageName
     */
    public function checkProduct(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        WishlistRepository $wishlistRepository,
        ProductRepository $productRepository,
        CustomPageRepository $customPageRepository,
        CouponCodesRepository $couponCodesRepository,
        ProductCategoryRepository $productCategoryRepository,
        $pageName
    ) {
        /**
         * @var $product Product
         */
        $product = $productRepository->findOneBy(
            [
                'customUrl' => $pageName
            ]
        );
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $productInWishlist = $wishlistRepository->findOneBy(
            [
                'product' => $product,
                'user' => $this->getUser(),
            ]
        );
        $discount = null;

        $form = $this->createForm(SoldFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Sold $sold
             */
            $sold = $form->getData();
            $quantityUser = $sold->getQuantity();
            $couponCode = $sold->getCouponCodeName();
            if ($quantityUser === null) {
                $this->addFlash('warning', 'Please insert correct number in field Quantity!');
                return $this->redirectToRoute(
                    'check_product',
                    [
                        'pageName' => $product->getCustomUrl()]
                );
            }
            if ($sold->getQuantity() > $product->getAvailableQuantity()) {
                $this->addFlash('warning', 'Not enough available quantity!');
                return $this->redirectToRoute(
                    'check_product',
                    [
                        'pageName' => $product->getCustomUrl()]
                );
            }
            if ($couponCode !== null) {
                /**
                 * @var $checkForCouponCode CouponCodes
                 */
                $checkForCouponCode = $couponCodesRepository->findOneBy(['codeName' => $couponCode]);
                if ($checkForCouponCode === null) {
                    $this->addFlash('warning', 'Invalid coupon code!');
                    return $this->redirectToRoute(
                        'check_product',
                        [
                            'pageName' => $product->getCustomUrl()]
                    );
                }
                if ($checkForCouponCode->getAllProducts()) {
                    $discount = $checkForCouponCode->getDiscount();
                } elseif ($checkForCouponCode->getCategoryId()) {
                    $productHasCategory = $productCategoryRepository->findOneBy([
                        'product' => $product,
                        'category' => $checkForCouponCode->getCategoryId()
                    ]);
                    if ($productHasCategory === null) {
                        $this->addFlash('warning', 'Invalid coupon code!');
                        return $this->redirectToRoute(
                            'check_product',
                            [
                                'pageName' => $product->getCustomUrl()]
                        );
                    }
                    $discount = $checkForCouponCode->getDiscount();
                } elseif ($checkForCouponCode->getProductId()) {
                    if ($checkForCouponCode->getProductId() != $product->getId()) {
                        $this->addFlash('warning', 'Invalid coupon code!');
                        return $this->redirectToRoute(
                            'check_product',
                            [
                                'pageName' => $product->getCustomUrl()]
                        );
                    }
                    $discount = $checkForCouponCode->getDiscount();
                } else {
                    $this->addFlash('warning', 'Invalid coupon code!');
                    return $this->redirectToRoute(
                        'check_product',
                        [
                            'pageName' => $product->getCustomUrl()]
                    );
                }
            }
            $sold->setUser($this->getUser());
            $sold->setProduct($product);
            $sold->setPrice($product->getPrice());
            $sold->setTotalPrice($sold->getQuantity() * $sold->getPrice());
            if ($discount !== null) {
                $sold->setDiscount($discount);
                $totalPrice = $sold->getTotalPrice();
                $discountAmount = str_replace('%', '', $discount);
                $discountReduce = $totalPrice * $discountAmount / 100;
                $afterDiscount = $totalPrice - $discountReduce;
                $sold->setAfterDiscount($afterDiscount);
                $entityManager->remove($checkForCouponCode);
            } else {
                $sold->setCouponCodeName('');
                $sold->setDiscount('0');
                $sold->setAfterDiscount($sold->getTotalPrice());
            }
            $sold->setConfirmed(0);
            $product->setAvailableQuantity($product->getAvailableQuantity() - $sold->getQuantity());
            $entityManager->persist($sold);
            $entityManager->flush();
            $this->addFlash('success', 'Bought the product!');
            return $this->redirectToRoute(
                'check_product',
                [
                    'pageName' => $product->getCustomUrl()]
            );
        }

        $formMail = $this->createForm(ContactFormType::class);
        $formMail->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formMail->isSubmitted() && $formMail->isValid()) {
            $formMailData = $formMail->getData();
            $name = $formMailData['name'];
            $from = $formMailData['from'];
            $message = $formMailData['message'];
            $to = $product->getUser()->getEmail();
            if (empty($name) || empty($from) || empty($message)) {
                $this->addFlash('warning', 'Please fill out all fields to send mail!');
                return $this->redirectToRoute(
                    'check_product',
                    [
                        'pageName' => $product->getCustomUrl()]
                );
            } elseif (filter_var($from, FILTER_VALIDATE_EMAIL)) {
                $this->addFlash(
                    'success',
                    'Mail sent. Name: ' . $name . ', from: ' . $from . ', to: '
                    . $to . ', message: ' . $message
                );
                return $this->redirectToRoute(
                    'check_product',
                    [
                        'pageName' => $product->getCustomUrl()]
                );
            } else {
                $this->addFlash('warning', 'Your email is not valid!');
                return $this->redirectToRoute(
                    'check_product',
                    [
                        'pageName' => $product->getCustomUrl()]
                );
            }
        }

        $formComment = $this->createForm(CommentFormType::class);
        $formComment->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formComment->isSubmitted() && $formComment->isValid()) {
            /**
             * @var Comment $comment
             */
            $comment = $formComment->getData();
            $comment->setUser($this->getUser());
            $comment->setProduct($product);
            $product->addComment($comment);
            $entityManager->flush();
            $this->addFlash('success', 'Comment added!');
            return $this->redirectToRoute(
                'check_product',
                [
                    'pageName' => $product->getCustomUrl()]
            );
        }

        return $this->render(
            '/advertisement/product_page.html.twig',
            [
                'product' => $product,
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'seller' => $product->getUser(),
                'productInWishlist' => $productInWishlist,
                'form' => $form->createView(),
                'commentForm' => $formComment->createView(),
                'emailForm' => $formMail->createView()
            ]
        );
    }

    /**
     * @Route("/items", name="my_items")
     * @param           CategoryRepository $categoryRepository
     * @param           ProductService $productService
     * @param           CustomPageRepository $customPageRepository
     * @param           Request $request
     * @return          Response
     */
    public function myItems(
        CategoryRepository $categoryRepository,
        ProductService $productService,
        CustomPageRepository $customPageRepository,
        Request $request
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $data = $productService->returnDataMyItems($request, $this->getUser()->getId());
        return $this->render(
            '/advertisement/my_items.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'myitems' => $data
            ]
        );
    }

    /**
     * @Route("/wishlist", name="my_wishlist")
     * @param              CategoryRepository $categoryRepository
     * @param              ProductService $productService
     * @param              EntityManagerInterface $entityManager
     * @param              CustomPageRepository $customPageRepository
     * @param              Request $request
     * @return             Response
     */
    public function myWishList(
        CategoryRepository $categoryRepository,
        ProductService $productService,
        EntityManagerInterface $entityManager,
        CustomPageRepository $customPageRepository,
        Request $request
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $wishlist = $productService->returnDataMyWishlist($request, $this->getUser()->getId());
        foreach ($wishlist as $wishlistProduct) {
            /**
             * @var Wishlist $wishlistProduct
             */
            if ($wishlistProduct->getNotify() === 0 && $wishlistProduct->getNotified() === 0) {
                continue;
            }
            if ($wishlistProduct->getNotify() === 0 && $wishlistProduct->getNotified() === 1) {
                $wishlistProduct->setNotified(0);
                $entityManager->persist($wishlistProduct);
                continue;
            }
            /**
             * @var Product $productCheck
             */
            $productCheck = $wishlistProduct->getProduct();
            if ($wishlistProduct->getNotify() === 1 && $productCheck->getAvailableQuantity() > 0) {
                $wishlistProduct->setNotify(0);
                $wishlistProduct->setNotified(1);
                $entityManager->persist($wishlistProduct);
            }
        }
        $entityManager->flush();
        return $this->render(
            '/advertisement/my_wish_list.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'mywishlist' => $wishlist
            ]
        );
    }

    /**
     * @Route("/add-to-wishlist/{id}", name="add_to_wishlist")
     * @param                          Product $product
     * @param                          EntityManagerInterface $entityManager
     * @param                          WishlistRepository $wishlistRepository
     * @return                         Response
     */
    public function addProductToWishList(
        Product $product,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository
    ) {
        $itemAlreadyExists = $wishlistRepository->findOneBy(
            [
                'product' => $product,
                'user' => $this->getUser(),
            ]
        );
        if ($itemAlreadyExists) {
            $this->addFlash('warning', 'Product already added to wishlist!');
            return $this->redirectToRoute(
                'check_product',
                [
                    'pageName' => $product->getCustomUrl()]
            );
        }
        $wishlist = new Wishlist();
        $wishlist->setProduct($product);
        $wishlist->setUser($this->getUser());
        $wishlist->setNotify(0);
        $wishlist->setNotified(0);
        $entityManager->persist($wishlist);
        $entityManager->flush();

        $this->addFlash('success', 'Added to wishlist!');
        return $this->redirectToRoute(
            'check_product',
            [
                'pageName' => $product->getCustomUrl()]
        );
    }

    /**
     * @Route("/remove-from-wishlist/{id}", name="remove_from_wishlist")
     * @param                               Product $product
     * @param                               EntityManagerInterface $entityManager
     * @param                               WishlistRepository $wishlistRepository
     * @return                              Response
     */
    public function removeProductToWishList(
        Product $product,
        EntityManagerInterface $entityManager,
        WishlistRepository $wishlistRepository
    ) {
        /**
         * @var Wishlist $removeProductFromWishlist
         */
        $removeProductFromWishlist = $wishlistRepository->findOneBy(
            [
                'product' => $product,
                'user' => $this->getUser(),
            ]
        );
        $entityManager->remove($removeProductFromWishlist);
        $entityManager->flush();

        $this->addFlash('success', 'Removed from wishlist!');
        return $this->redirectToRoute(
            'check_product',
            [
                'pageName' => $product->getCustomUrl()]
        );
    }

    /**
     * @Route("/exceluser", name="exceluser")
     * @return              \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws              \PhpOffice\PhpSpreadsheet\Exception
     * @throws              \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @param               SoldRepository $soldRepository
     */
    public function excelUser(SoldRepository $soldRepository)
    {
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(
            new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder()
        );
        $spreadsheet = new Spreadsheet();
        $sold = $soldRepository->findBy(['user' => $this->getUser()], ['product' => 'ASC']);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Product name');
        $sheet->setCellValue('B1', 'Seller');
        $sheet->setCellValue('C1', 'Email');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->setCellValue('E1', 'Price');
        $sheet->setCellValue('F1', 'Total Price');
        $sheet->setCellValue('G1', 'Bought at');
        $i = 2;
        foreach ($sold as $item) {
            /**
             * @var Sold $item
             */
            $sheet->setCellValue('A' . $i, $item->getProduct()->getName());
            $sheet->setCellValue('B' . $i, $item->getProduct()->getUser()->getFullName());
            $sheet->setCellValue('C' . $i, $item->getProduct()->getUser()->getEmail());
            $sheet->setCellValue('D' . $i, $item->getQuantity());
            $sheet->setCellValue('E' . $i, $item->getPrice());
            $sheet->setCellValue('F' . $i, $item->getTotalPrice());
            $sheet->setCellValue('G' . $i, $item->getBoughtAt()->format('Y-m-d H:i:s'));
            $i++;
        }

        $sheet->setTitle("Bought items");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'bought_products.xlsx';
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($temp_file);

        // Return the excel file as an attachment
        return $this->file($temp_file, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/custom-page/{pageName}", name="render_custom_page")
     * @param                            CustomPageRepository $customPageRepository
     * @param                            CategoryRepository $categoryRepository
     * @var                              $pageName
     * @return                           Response
     */
    public function renderCustomPage(
        CategoryRepository $categoryRepository,
        CustomPageRepository $customPageRepository,
        $pageName
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $customPage = $customPageRepository->findOneBy(['pageName' => $pageName]);
        return $this->render(
            '/advertisement/custom_page_layout.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'customPage' => $customPage,
            ]
        );
    }
}
