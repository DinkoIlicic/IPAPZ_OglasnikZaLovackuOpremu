<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:20 AM
 */

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Entity\PaymentTransaction;
use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class PaymentMethodController extends AbstractController
{
    /**
     * @Route("/admin/payment-methods/", name="show_payment_methods")
     * @param                       PaymentMethodRepository $paymentMethodRepository
     * @return                      \Symfony\Component\HttpFoundation\Response
     */
    public function showPaymentMethods(PaymentMethodRepository $paymentMethodRepository)
    {
        $paymentMethods = $paymentMethodRepository->findAll();
        return $this->render(
            'admin/payment_methods.html.twig',
            [
                'paymentMethods' => $paymentMethods,
            ]
        );
    }

    /**
     * @Route("/admin/enable-payment-method/{id}", name="enable_payment_method")
     * @param                       PaymentMethod $paymentMethod
     * @param                       EntityManagerInterface $entityManager
     * @return                      \Symfony\Component\HttpFoundation\Response
     */
    public function enablePaymentMethods(PaymentMethod $paymentMethod, EntityManagerInterface $entityManager)
    {
        if ($paymentMethod->getEnabled() === true) {
            $paymentMethod->setEnabled(false);
            $this->addFlash('success', 'Payment method disabled!');
        } elseif ($paymentMethod->getEnabled() === false) {
            $paymentMethod->setEnabled(true);
            $this->addFlash('success', 'Payment method enabled!');
        }

        $entityManager->persist($paymentMethod);
        $entityManager->flush();
        return $this->redirectToRoute('show_payment_methods');
    }

    /**
     * @Route("/admin/download/{fileName}",name="pdf_download")
     * @param $fileName
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function downloadPdf($fileName)
    {
        $filePath = $this->getParameter('kernel.project_dir') . '/public/invoice/' . $fileName;

        $response = new Response();
        $response->headers->set('Content-type', 'application/octet-stream');
        $response->headers->set(
            'Content-Disposition',
            sprintf('attachment; filename="%s"', $fileName)
        );
        $response->setContent(file_get_contents($filePath));
        $response->setStatusCode(200);
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        return $response;
    }

    /**
     * @Route("/admin/confirm-payment-transaction/{paymentTransaction}",name="confirm_payment_transaction")
     * @param PaymentTransaction $paymentTransaction
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function confirmInvoicePayment(PaymentTransaction $paymentTransaction, EntityManagerInterface $entityManager)
    {
        if ($paymentTransaction->getConfirmed() === true) {
            $paymentTransaction->setConfirmed(false);
            $this->addFlash('success', 'Payment transaction confirm removed!');
        } elseif ($paymentTransaction->getConfirmed() === false) {
            $paymentTransaction->setConfirmed(true);
            $paymentTransaction->onPrePersistPaidAt();
            $this->addFlash('success', 'Payment transaction confirmed!');
        }

        $entityManager->persist($paymentTransaction);
        $entityManager->flush();
        return $this->redirectToRoute(
            'view_sold_items_per_user_payment_method',
            [
                'id' => $paymentTransaction->getId()
            ]
        );
    }

    /**
     * @Route("/admin/delete-payment-transaction/{paymentTransaction}",name="delete_payment_transaction")
     * @param EntityManagerInterface $entityManager
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteInvoicePayment(EntityManagerInterface $entityManager, PaymentTransaction $paymentTransaction)
    {
        $entityManager->remove($paymentTransaction);
        $entityManager->flush();
        $this->addFlash('success', 'Invoice deleted!');
        return $this->redirectToRoute('view_sold_items_per_person');
    }
}
