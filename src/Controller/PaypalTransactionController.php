<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/21/19
 * Time: 1:57 PM
 */

namespace App\Controller;

use App\Entity\PaypalTransaction;
use App\Entity\Sold;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaypalTransactionController extends AbstractController
{
    /**
     * @Route("/profile/paypal-pay/{id}", name="paypal_pay")
     * @param                      Sold $sold
     * @return                     Response
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
     * @Route("/profile/paypal-payment/{id}", name="paypal_payment")
     * @param Sold $sold
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function payment(EntityManagerInterface $entityManager, Sold $sold)
    {
        $gateway = self::gateway();
        $amount = $sold->getAfterDiscount();
        $nonce = $_POST["payment_method_nonce"];
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
        $paypalTransaction = new PaypalTransaction();
        $paypalTransaction->setUser($sold->getUser());
        $paypalTransaction->setSoldProduct($sold);
        $paypalTransaction->setTransactionId($transaction->id);
        $paypalTransaction->setConfirmed(true);
        $entityManager->persist($sold);
        $entityManager->persist($paypalTransaction);
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
}
