<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 9:53 AM
 */

namespace App\Controller;

use App\Entity\Coupon;
use App\Entity\CouponCodes;
use App\Entity\RandomCodeGenerator;
use App\Form\CouponCodesFormType;
use App\Form\CouponFormType;
use App\Form\RemoveCouponCodesFormType;
use App\Repository\CouponCodesRepository;
use App\Repository\CouponRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class CouponController extends AbstractController
{
    /**
     * @Route("/admin/coupons/", name="show_coupons")
     * @param                    CouponRepository $couponRepository
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function showCouponGroup(CouponRepository $couponRepository)
    {
        $coupons = $couponRepository->findAll();

        return $this->render(
            '/admin/coupons.html.twig',
            [
                'coupons' => $coupons,
            ]
        );
    }

    /**
     * @Route("/admin/add-coupon/", name="add_coupon_group")
     * @param                       Request $request
     * @param                       EntityManagerInterface $entityManager
     * @return                      \Symfony\Component\HttpFoundation\Response
     */
    public function addCouponGroup(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CouponFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $couponGroup = $form->getData();
            $entityManager->persist($couponGroup);
            $entityManager->flush();
            $this->addFlash('success', 'Coupon group added!');
            return $this->redirectToRoute('show_coupons');
        };

        return $this->render(
            '/admin/add_coupon_group.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/add-coupon-codes/{id}", name="add_coupon_codes")
     * @param                                 Request $request
     * @param                                 EntityManagerInterface $entityManager
     * @param                                 Coupon $coupon
     * @return                                \Symfony\Component\HttpFoundation\Response
     */
    public function addCouponCodes(Request $request, EntityManagerInterface $entityManager, Coupon $coupon)
    {
        $form = $this->createForm(CouponCodesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $codesAmount = $form->get('amount')->getData();
            if (!($codesAmount >= 1) || !($codesAmount <= 10000)) {
                $this->addFlash('warning', 'Amount must be between 1 and 10000!');
                return $this->redirectToRoute(
                    'add_coupon_codes',
                    [
                        'id' => $coupon->getId()]
                );
            }

            $codesAll = $form->get('allProducts')->getData();
            $codesCategory = $form->get('category')->getData();
            $codesProduct = $form->get('product')->getData();
            if (!$codesAll && $codesCategory === null && $codesProduct === null) {
                $this->addFlash(
                    'warning',
                    'Please choose at least one of the 3 given options (All products, category or product)!'
                );
                return $this->redirectToRoute(
                    'add_coupon_codes',
                    [
                        'id' => $coupon->getId()]
                );
            }

            $codesNames = new RandomCodeGenerator();
            $codesArrayNames = $codesNames->generate($codesAmount);
            for ($i = 0; $i < $codesAmount; $i++) {
                $code = new CouponCodes();
                $code->setCodeGroup($coupon);
                $code->setCodeName($codesArrayNames[$i]);
                if ($codesAll) {
                    $code->setAllProducts(1);
                    $code->setProductId(0);
                    $code->setCategoryId(0);
                } elseif ($codesCategory !== null) {
                    $code->setCategoryId($codesCategory->getId());
                    $code->setAllProducts(0);
                    $code->setProductId(0);
                } elseif ($codesProduct !== null) {
                    $code->setProductId($codesProduct->getId());
                    $code->setAllProducts(0);
                    $code->setCategoryId(0);
                }

                $code->setDiscount($coupon->getDiscount());
                $entityManager->persist($code);
            }

            $entityManager->flush();
            $this->addFlash(
                'success',
                'Coupons added!'
            );
            return $this->redirectToRoute(
                'show_coupons'
            );
        };

        return $this->render(
            '/admin/add_coupon_codes.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/delete-coupon-codes/{id}", name="delete_coupon_codes")
     * @param                                    Request $request
     * @param                                    Coupon $coupon
     * @param                                    EntityManagerInterface $entityManager
     * @return                                   \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCouponCodes(Request $request, Coupon $coupon, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(RemoveCouponCodesFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $startId = $form->get('startId')->getData();
            $endId = $form->get('endId')->getData();
            $deleteQuery = $entityManager->createQuery(
                '
                Delete 
                From App\Entity\CouponCodes cc
                WHERE cc.id >= :startId AND cc.id <= :endId AND cc.codeGroup = :coupon
                '
            );
            $deleteQuery->setParameter('startId', $startId);
            $deleteQuery->setParameter('endId', $endId);
            $deleteQuery->setParameter('coupon', $coupon);
            $deleteQuery->execute();
            $this->addFlash(
                'success',
                'Coupon codes removed!'
            );
            return $this->redirectToRoute(
                'show_coupons'
            );
        };

        return $this->render(
            '/admin/remove_coupon_codes.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/excel-coupon-codes/{id}", name="create_excel_coupon_codes_file")
     * @return              \Symfony\Component\HttpFoundation\BinaryFileResponse
     * @throws              \PhpOffice\PhpSpreadsheet\Exception
     * @throws              \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @param               CouponCodesRepository $couponCodesRepository
     * @param               Coupon $coupon
     */
    public function excelCouponCodes(CouponCodesRepository $couponCodesRepository, Coupon $coupon)
    {
        \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder(
            new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder()
        );
        $spreadsheet = new Spreadsheet();
        $couponCodes = $couponCodesRepository->findBy(['codeGroup' => $coupon], ['id' => 'ASC']);

        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Id');
        $sheet->setCellValue('B1', 'Coupon Code Name');
        $sheet->setCellValue('C1', 'Coupon Group');
        $sheet->setCellValue('D1', 'Discount');
        $sheet->setCellValue('E1', 'All Products');
        $sheet->setCellValue('F1', 'Category');
        $sheet->setCellValue('G1', 'Product');
        $i = 2;
        foreach ($couponCodes as $item) {
            /**
             * @var CouponCodes $item
             */
            $sheet->setCellValue('A' . $i, $item->getId());
            $sheet->setCellValue('B' . $i, $item->getCodeName());
            $sheet->setCellValue('C' . $i, $item->getCodeGroup()->getCodeGroupName());
            $sheet->setCellValue('D' . $i, $item->getDiscount());
            $sheet->setCellValue('E' . $i, $item->getAllProducts());
            $sheet->setCellValue('F' . $i, $item->getCategoryId());
            $sheet->setCellValue('G' . $i, $item->getProductId());
            $i++;
        }

        $sheet->setTitle("Coupon Codes");

        // Create your Office 2007 Excel (XLSX Format)
        $writer = new Xlsx($spreadsheet);

        // Create a Temporary file in the system
        $fileName = 'coupon_codes.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), $fileName);

        // Create the excel file in the tmp directory of the system
        $writer->save($tempFile);

        // Return the excel file as an attachment
        return $this->file($tempFile, $fileName, ResponseHeaderBag::DISPOSITION_INLINE);
    }
}
