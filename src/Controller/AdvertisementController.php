<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 08:38
 */

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Product;
use App\Entity\Shipping;
use App\Entity\Sold;
use App\Entity\Wishlist;
use App\Form\CommentFormType;
use App\Form\ContactFormType;
use App\Form\NewAddressFormType;
use App\Form\PaymentOptionFormType;
use App\Form\SellerFormType;
use App\Form\SoldFormType;
use App\Repository\CategoryRepository;
use App\Repository\CouponCodesRepository;
use App\Repository\CustomPageRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\PaymentTransactionRepository;
use App\Repository\ProductCategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SellerRepository;
use App\Repository\ShippingRepository;
use App\Repository\SoldRepository;
use App\Repository\WishlistRepository;
use App\Service\ProductService;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class AdvertisementController extends AbstractController
{
    /**
     * @Route("/", name="advertisement_index")
     * @param      CategoryRepository $categoryRepository
     * @param      ProductService $productService
     * @param      Request $request
     * @param      CustomPageRepository $customPageRepository
     * @return     \Symfony\Component\HttpFoundation\Response
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
     * @Route("/redirect-to-index", name="redirect_to_index")
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function redirectToIndex()
    {
        return $this->redirectToRoute('advertisement_index');
    }

    /**
     * @param CategoryRepository $categoryRepository
     * @param CustomPageRepository $customPageRepository
     * @return array
     */
    private function findDataForHeader(
        CustomPageRepository $customPageRepository,
        CategoryRepository $categoryRepository
    ) {
        $allCustomPages = $customPageRepository->findBy(['visibilityAdmin' => true]);
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
    private function getAllVisibleCategories(CategoryRepository $categoryRepository)
    {
        $categories = $categoryRepository->findBy(
            [
                'visibilityAdmin' => 1
            ],
            [
                'name' => 'ASC'
            ]
        );
        return $categories;
    }

    /**
     * @Route("/category/{urlName}", name="show_categories")
     * @param                          CategoryRepository $categoryRepository
     * @param                          ProductService $productService
     * @param                          Request $request
     * @param                          $urlName
     * @param                          CustomPageRepository $customPageRepository
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function showProductsPerCategory(
        $urlName,
        CategoryRepository $categoryRepository,
        ProductService $productService,
        Request $request,
        CustomPageRepository $customPageRepository
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $data = $productService->returnDataPerCategory($request, $urlName);
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
     * @return                     \Symfony\Component\HttpFoundation\Response
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
             * @var \App\Entity\Seller $seller
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
     * @Route("/product/{pageName}", name="check_product")
     * @param                              CategoryRepository $categoryRepository
     * @param                              EntityManagerInterface $entityManager
     * @param                              WishlistRepository $wishlistRepository
     * @param                              ProductRepository $productRepository
     * @param                              CustomPageRepository $customPageRepository
     * @param                              CouponCodesRepository $couponCodesRepository
     * @param                              ProductCategoryRepository $productCategoryRepository
     * @param                              Request $request
     * @return                             \Symfony\Component\HttpFoundation\Response
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
        if (!$product) {
            throw $this->createNotFoundException("Page not found.");
        }

        $productInWishlist = $wishlistRepository->findOneBy(
            [
                'product' => $product,
                'user' => $this->getUser(),
            ]
        );
        $form = $this->createForm(SoldFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $form->isSubmitted() && $form->isValid()) {
            $sold = $form->getData();
            return self::userBuyProduct(
                $sold,
                $product,
                $entityManager,
                $couponCodesRepository,
                $productCategoryRepository
            );
        }

        $formMail = $this->createForm(ContactFormType::class);
        $formMail->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formMail->isSubmitted() && $formMail->isValid()) {
            $formMailData = $formMail->getData();
            self::sendMail($formMailData, $product);
        }

        $formComment = $this->createForm(CommentFormType::class);
        $formComment->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formComment->isSubmitted() && $formComment->isValid()) {
            $comment = $formComment->getData();
            self::commentOnProduct($comment, $product, $entityManager);
        }

        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
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

    private function userBuyProduct(
        Sold $sold,
        Product $product,
        EntityManagerInterface $entityManager,
        CouponCodesRepository $couponCodesRepository,
        ProductCategoryRepository $productCategoryRepository
    ) {
        $discount = null;
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
            $validCouponCode = self::checkCouponCode(
                $couponCode,
                $product,
                $productCategoryRepository,
                $couponCodesRepository
            );

            if (!$validCouponCode) {
                $this->addFlash('warning', 'Invalid coupon code!');
                return $this->redirectToRoute(
                    'check_product',
                    [
                        'pageName' => $product->getCustomUrl()]
                );
            }

            $checkForCouponCode = $couponCodesRepository->findOneBy(['codeName' => $couponCode]);
            $discount = $checkForCouponCode->getDiscount();
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
            $sold->setShippingPrice(0);
            $sold->setToPay($afterDiscount);
            /**
             * @var \App\Entity\CouponCodes $checkForCouponCode
             */
            $entityManager->remove($checkForCouponCode);
        } else {
            $sold->setCouponCodeName('');
            $sold->setDiscount('0');
            $sold->setAfterDiscount($sold->getTotalPrice());
            $sold->setShippingPrice(0);
            $sold->setToPay($sold->getTotalPrice());
        }

        $sold->setConfirmed(0);
        $product->setAvailableQuantity($product->getAvailableQuantity() - $sold->getQuantity());
        $entityManager->persist($sold);
        $entityManager->flush();
        return $this->redirectToRoute(
            'choose_payment_option_user',
            [
                'id' => $sold->getId()
            ]
        );
    }


    /**
     * @Route("/choose-payment-option/{id}", name="choose_payment_option_user")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @param CustomPageRepository $customPageRepository
     * @param EntityManagerInterface $entityManager
     * @param ShippingRepository $shippingRepository
     * @param PaymentTransactionRepository $paymentTransactionRepository
     * @param Sold $sold
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choosePaymentOption(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository,
        CustomPageRepository $customPageRepository,
        Sold $sold,
        ShippingRepository $shippingRepository,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        $checkIfExists = $paymentTransactionRepository->findOneBy(['soldProduct' => $sold]);
        if ($checkIfExists || ($this->getUser() !== $sold->getUser())) {
            throw $this->createNotFoundException("Page not found.");
        }

        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);

        $formNewAddress = $this->createForm(NewAddressFormType::class);
        $formNewAddress->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $formNewAddress->isSubmitted() && $formNewAddress->isValid()) {
            /**
             * @var $newAddress \App\Entity\UserAddress
             */
            $newAddress = $formNewAddress->getData();
            $newAddress->setUser($this->getUser());
            $entityManager->persist($newAddress);
            $entityManager->flush();
        }

        $paymentOption = $this->createForm(PaymentOptionFormType::class, null, array('user' => $this->getUser()));
        $paymentOption->handleRequest($request);
        if ($this->isGranted('ROLE_USER') && $paymentOption->isSubmitted() && $paymentOption->isValid()) {
            /**
             * @var \App\Entity\PaymentMethod $paymentChosen
             */
            $paymentChosen = $paymentOption->get('payment')->getData();
            $optionForPay = $paymentChosen->getMethod();
            /**
             * @var \App\Entity\UserAddress $newAddress
             */
            $newAddress = $paymentOption->get('address')->getData();
            $sold->setAddress($newAddress);
            $sold->setPaymentMethod($optionForPay);
            $country = $newAddress->getCountry();
            $checkForCountry = $shippingRepository->findOneBy(['country' => $country]);
            if ($checkForCountry instanceof Shipping) {
                /**
                 * @var \App\Entity\Shipping $checkForCountry
                 */
                $price = $checkForCountry->getPrice();
                if ($price === null) {
                    /**
                     * @var \App\Entity\Shipping $defaultPrice
                     */
                    $defaultPrice = $shippingRepository->findOneBy(['country' => 'default']);
                    $price = $defaultPrice->getPrice();
                }
            }

            $sold->setShippingPrice($price);
            $currentAmountToPay = $sold->getAfterDiscount();
            $sold->setToPay($currentAmountToPay + $price);
            $entityManager->merge($sold);
            $entityManager->flush();
            if ($optionForPay == 'Paypal') {
                return $this->redirectToRoute(
                    'paypal_show',
                    [
                        'id' => $sold->getId()
                    ]
                );
            } elseif ($optionForPay == 'Invoice') {
                return $this->redirectToRoute(
                    'invoice_show',
                    [
                        'id' => $sold->getId()
                    ]
                );
            }
        }

        return $this->render(
            '/advertisement/choose_payment_option.html.twig',
            [
                'newAddress' => $formNewAddress->createView(),
                'paymentOption' => $paymentOption->createView(),
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
            ]
        );
    }

    /**
     * @param $couponCode
     * @param Product $product
     * @param ProductCategoryRepository $productCategoryRepository
     * @param CouponCodesRepository $couponCodesRepository
     * @return bool
     */
    private function checkCouponCode(
        $couponCode,
        Product $product,
        ProductCategoryRepository $productCategoryRepository,
        CouponCodesRepository $couponCodesRepository
    ) {

        /**
         * @var $checkForCouponCode \App\Entity\CouponCodes
         */
        $checkForCouponCode = $couponCodesRepository->findOneBy(['codeName' => $couponCode]);
        if ($checkForCouponCode === null) {
            return false;
        }

        if ($checkForCouponCode->getAllProducts()) {
            return true;
        }

        if ($checkForCouponCode->getCategoryId()) {
            $productHasCategory = $productCategoryRepository->findOneBy(
                [
                    'product' => $product,
                    'category' => $checkForCouponCode->getCategoryId()
                ]
            );
            if ($productHasCategory === null) {
                return false;
            }

            return true;
        }

        if ($checkForCouponCode->getProductId()) {
            if ($checkForCouponCode->getProductId() != $product->getId()) {
                return false;
            }

            return true;
        }

        return true;
    }

    /**
     * @param Comment $comment
     * @param Product $product
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function commentOnProduct(Comment $comment, Product $product, EntityManagerInterface $entityManager)
    {
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

    /**
     * @param $formMailData
     * @param Product $product
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function sendMail($formMailData, Product $product)
    {
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
        }

        if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
            $this->addFlash('warning', 'Your email is not valid!');
            return $this->redirectToRoute(
                'check_product',
                [
                    'pageName' => $product->getCustomUrl()]
            );
        }

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
    }

    /**
     * @Route("/items", name="my_items")
     * @param           CategoryRepository $categoryRepository
     * @param           ProductService $productService
     * @param           CustomPageRepository $customPageRepository
     * @param           PaymentMethodRepository $paymentMethodRepository
     * @param           Request $request
     * @return          \Symfony\Component\HttpFoundation\Response
     */
    public function myItems(
        CategoryRepository $categoryRepository,
        ProductService $productService,
        CustomPageRepository $customPageRepository,
        PaymentMethodRepository $paymentMethodRepository,
        Request $request
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $data = $productService->returnDataMyItems($request, $this->getUser()->getId());
        $paymentOptions = $paymentMethodRepository->findAll();
        $paypal = $paymentOptions[0];
        $invoice = $paymentOptions[1];
        return $this->render(
            '/advertisement/my_items.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'myitems' => $data,
                'paypal' => $paypal,
                'invoice' => $invoice,
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
     * @return             \Symfony\Component\HttpFoundation\Response
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
     * @return                         \Symfony\Component\HttpFoundation\Response
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
     * @return                              \Symfony\Component\HttpFoundation\Response
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
        if (!$removeProductFromWishlist) {
            $this->addFlash('warning', 'Product not found in Your wishlist!');
            return $this->redirectToRoute(
                'check_product',
                [
                    'pageName' => $product->getCustomUrl()]
            );
        }

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
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($tempFile);

        // Return the excel file as an attachment
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }

    /**
     * @Route("/custom-page/{customUrl}", name="render_custom_page")
     * @param                            CustomPageRepository $customPageRepository
     * @param                            CategoryRepository $categoryRepository
     * @var                              $customUrl
     * @return                           \Symfony\Component\HttpFoundation\Response
     */
    public function renderCustomPage(
        CategoryRepository $categoryRepository,
        CustomPageRepository $customPageRepository,
        $customUrl
    ) {
        $arrayWithHeaderData = self::findDataForHeader($customPageRepository, $categoryRepository);
        $customPage = $customPageRepository->findOneBy(['customUrl' => $customUrl, 'visibilityAdmin' => true]);
        if (!$customPage) {
            return $this->redirectToRoute(
                'advertisement_index'
            );
        }

        return $this->render(
            '/advertisement/custom_page_layout.html.twig',
            [
                'pages' => $arrayWithHeaderData['customPages'],
                'categories' => $arrayWithHeaderData['categories'],
                'customPage' => $customPage,
            ]
        );
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function searchBar()
    {
        $form = $this->createFormBuilder(null)
            ->add(
                "query",
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => 'Enter here'
                    ],
                    'label' => ' '
                ]
            )
            ->getForm();
        return $this->render(
            'advertisement/search.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }
}
