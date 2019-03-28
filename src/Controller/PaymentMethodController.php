<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:20 AM
 */

namespace App\Controller;

use App\Entity\PaymentMethod;
use App\Repository\PaymentMethodRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
}
