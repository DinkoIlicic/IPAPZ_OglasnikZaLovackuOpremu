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
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class PaymentTransactionController extends AbstractController
{
    /**
     * @Route("/transaction/paypal-pay/{id}", name="paypal_pay")
     * @param                      Sold $sold
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function paypalShow(Sold $sold)
    {
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
     * @param Sold $sold
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function payment(EntityManagerInterface $entityManager, Sold $sold, Request $request)
    {
        $gateway = self::gateway();
        $amount = $sold->getAfterDiscount();
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
        $paymentTransaction->onPrePersistChosenAt();
        $paymentTransaction->onPrePersistPaidAt();
        $entityManager->persist($sold);
        $entityManager->persist($paymentTransaction);
        $entityManager->flush();
        $this->addFlash('success', 'Payment successful!');
        return $this->redirectToRoute('my_items');
    }

    public function gateway()
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
     * @Route("/transaction/invoice-pay/{id}", name="invoice_pay")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function choseOnDeliveryMethod(Sold $sold, EntityManagerInterface $entityManager)
    {
        if ($sold === null) {
            return $this->redirectToRoute('advertisement_index');
        }

        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setSoldProduct($sold);
        $paymentTransaction->setUser($this->getUser());
        $paymentTransaction->setConfirmed(0);
        $paymentTransaction->onPrePersistChosenAt();
        $paymentTransaction->setMethod('Invoice');
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
    public function createDomPdf(Sold $sold, PaymentTransaction $invoice, EntityManagerInterface $entityManager)
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

        $publicDirectory = '../public/invoice/';

        $pdfFilepath =  $publicDirectory . $pdfName;

        file_put_contents($pdfFilepath, $output);
    }
}
