<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/21/19
 * Time: 1:57 PM
 */

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaypalTransactionController extends AbstractController
{
    /**
     * @Route("/profile/paypal-pay/", name="paypal_pay")
     * @return                     Response
     */
    public function paypalShow()
    {
        $gateway = self::gateway();
        return $this->render(
            'paypal/paypal.html.twig',
            [
                'gateway' => $gateway,
            ]
        );
    }

    /**
     * @Route("/profile/paypal-payment/", name="paypal_payment")
     * @param EntityManagerInterface $entityManager
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function payment(EntityManagerInterface $entityManager)
    {
        $gateway = self::gateway();
        $amount = 1;
        $nonce = $_POST["payment_method_nonce"];
        $result = $gateway->transaction()->sale(
            [
                'amount' => $amount,
                'paymentMethodNonce' => $nonce
            ]
        );
        $transaction = $result->transaction;
        $this->addFlash('success', 'Payment successful!');
        return $this->redirectToRoute('advertisement_index');
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

    public function checkCouponCode()
    {
        
    }
}
