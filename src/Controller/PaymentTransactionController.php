<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/25/19
 * Time: 1:21 PM
 */

namespace App\Controller;

use App\Entity\PaymentTransaction;
use App\Entity\Sold;
use App\Repository\PaymentTransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class PaymentTransactionController extends AbstractController
{
    /**
     * @Route("/transaction/paypal-show/{id}", name="paypal_show")
     * @param                      Sold $sold
     * @param                      PaymentTransactionRepository $paymentTransactionRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function paypalShow(
        Sold $sold,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        $checkIfExists = $paymentTransactionRepository->findOneBy(['soldProduct' => $sold]);
        if ($checkIfExists !== null) {
            return $this->redirectToRoute('advertisement_index');
        }

        if ($this->getUser() !== $sold->getUser()) {
            return $this->redirectToRoute('advertisement_index');
        }

        $gateway = self::gateway();
        return $this->render(
            'paypal/paypal.html.twig',
            [
                'gateway' => $gateway,
                'sold' => $sold,
            ]
        );
    }

    /**
     * @Route("/transaction/paypal-payment/{id}", name="paypal_payment")
     * @param Request $request
     * @param PaymentTransactionRepository $paymentTransactionRepository
     * @param Sold $sold
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function payment(
        EntityManagerInterface $entityManager,
        Sold $sold,
        Request $request,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        $checkIfExists = $paymentTransactionRepository->findOneBy(['soldProduct' => $sold]);
        if ($checkIfExists !== null) {
            return $this->redirectToRoute('advertisement_index');
        }

        if ($this->getUser() !== $sold->getUser()) {
            return $this->redirectToRoute('advertisement_index');
        }

        $gateway = self::gateway();
        $amount = $sold->getToPay();
        $nonce = $request->get('payment_method_nonce');
        $result = $gateway->transaction()->sale(
            [
                'amount' => $amount,
                'paymentMethodNonce' => $nonce
            ]
        );
        $transaction = $result->transaction;
        if ($transaction == null) {
            $this->addFlash('warning', 'Payment unsuccessful!');
            return $this->redirectToRoute('my_items');
        }

        $sold->setConfirmed(1);
        $sold->setPaymentMethod('Paypal');
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setUser($sold->getUser());
        $paymentTransaction->setSoldProduct($sold);
        $paymentTransaction->setTransactionId($transaction->id);
        $paymentTransaction->setConfirmed(true);
        $paymentTransaction->setMethod('Paypal');
        $paymentTransaction->setUserAddress($sold->getAddress());
        $paymentTransaction->onPrePersistChosenAt();
        $paymentTransaction->onPrePersistPaidAt();
        $entityManager->persist($sold);
        $entityManager->persist($paymentTransaction);
        $entityManager->flush();
        $this->addFlash('success', 'Payment successful!');
        return $this->redirectToRoute('my_items');
    }

    private function gateway()
    {
        return $gateway = new \Braintree_Gateway(
            [
                'environment' => 'sandbox',
                'merchantId' => 'x956q5b36cwdwtsm',
                'publicKey' => 'frsxwfmj29xxgm74',
                'privateKey' => 'e300308a809c4ea50d66e5dba62a48fb'
            ]
        );
    }

    /**
     * @Route("/transaction/invoice-show/{id}", name="invoice_show")
     * @param                      Sold $sold
     * @param                      PaymentTransactionRepository $paymentTransactionRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function invoiceShow(Sold $sold, PaymentTransactionRepository $paymentTransactionRepository)
    {
        $checkIfExists = $paymentTransactionRepository->findOneBy(['soldProduct' => $sold]);
        if ($checkIfExists !== null) {
            return $this->redirectToRoute('advertisement_index');
        }

        if ($this->getUser() !== $sold->getUser()) {
            return $this->redirectToRoute('advertisement_index');
        }

        return $this->render(
            'invoice/invoice.html.twig',
            [
                'sold' => $sold,
            ]
        );
    }

    /**
     * @Route("/transaction/invoice-pay/{id}", name="invoice_pay")
     * @param EntityManagerInterface $entityManager
     * @param PaymentTransactionRepository $paymentTransactionRepository
     * @param Sold $sold
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choseOnDeliveryMethod(
        Sold $sold,
        EntityManagerInterface $entityManager,
        PaymentTransactionRepository $paymentTransactionRepository
    ) {
        $checkIfExists = $paymentTransactionRepository->findOneBy(['soldProduct' => $sold]);
        if ($checkIfExists !== null) {
            return $this->redirectToRoute('advertisement_index');
        }

        if ($this->getUser() !== $sold->getUser()) {
            return $this->redirectToRoute('advertisement_index');
        }

        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setSoldProduct($sold);
        $paymentTransaction->setUser($this->getUser());
        $paymentTransaction->setConfirmed(0);
        $paymentTransaction->onPrePersistChosenAt();
        $paymentTransaction->setMethod('Invoice');
        $paymentTransaction->setUserAddress($sold->getAddress());
        $sold->setConfirmed(true);
        $sold->setPaymentMethod('Invoice');
        $entityManager->persist($paymentTransaction);
        $entityManager->persist($sold);
        $entityManager->flush();

        self:$this->createDomPdf($sold, $paymentTransaction, $entityManager);

        $this->addFlash('success', 'Invoice successfully chosen!');
        return $this->redirectToRoute('my_items');
    }

    /**
     * @param Sold $sold
     * @param PaymentTransaction $invoice
     * @param EntityManagerInterface $entityManager
     */
    private function createDomPdf(Sold $sold, PaymentTransaction $invoice, EntityManagerInterface $entityManager)
    {
        $pdfOptions = new Options();
        $pdfOptions->set('defaultFont', 'Arial');

        $domPdf = new Dompdf($pdfOptions);

        $html = $this->renderView(
            'advertisement/my_pdf.html.twig',
            [
                'title' => "Welcome to our PDF Test",
                'sold' => $sold,
                'invoice' => $invoice
            ]
        );

        $pdfName = date("Y") . $invoice->getId() . $sold->getUser()->getId() . '.pdf';
        $invoice->setTransactionId($pdfName);
        $entityManager->persist($invoice);
        $entityManager->flush();

        $domPdf->loadHtml($html);

        $domPdf->setPaper('A4', 'portrait');

        $domPdf->render();

        $output = $domPdf->output();

        $publicDirectory = self::returnPathToInvoice();

        $pdfFilepath =  $publicDirectory . $pdfName;

        file_put_contents($pdfFilepath, $output);
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
        $invoice = $paymentTransaction->getMethod();
        if ($invoice !== 'Invoice') {
            return $this->redirectToRoute('admin_index');
        }

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
     * @param Filesystem $filesystem
     * @param PaymentTransactionRepository $paymentTransactionRepository
     * @param PaymentTransaction $paymentTransaction
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deletePaymentTransaction(
        EntityManagerInterface $entityManager,
        PaymentTransaction $paymentTransaction,
        PaymentTransactionRepository $paymentTransactionRepository,
        Filesystem $filesystem
    ) {
        $checkIfExists = $paymentTransactionRepository->findOneBy(['id' => $paymentTransaction->getId()]);
        if ($checkIfExists === null) {
            return $this->redirectToRoute('admin_index');
        }

        if ($paymentTransaction->getMethod() === 'Invoice') {
            $publicDirectory = self::returnPathToInvoice();
            $filesystem->remove($publicDirectory . $paymentTransaction->getTransactionId());
        }

        /**
         * @var \App\Entity\Sold $soldProduct
         */
        $soldProduct = $paymentTransaction->getSoldProduct();
        $soldProduct->setConfirmed(false);
        $soldProduct->setPaymentMethod(null);
        $soldProduct->setAddress(null);
        $entityManager->remove($paymentTransaction);
        $entityManager->flush();
        $this->addFlash('success', 'Payment deleted!');
        return $this->redirectToRoute('view_sold_items_per_person');
    }

    private function returnPathToInvoice()
    {
        $publicDirectory = '../public/invoice/';
        return $publicDirectory;
    }
}
