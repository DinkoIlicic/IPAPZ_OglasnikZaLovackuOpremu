<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/25/19
 * Time: 8:15 AM
 */

namespace App\Controller;

use App\Entity\OnDeliveryTransaction;
use App\Entity\Sold;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class OnDeliveryController extends AbstractController
{
    /**
     * @Route("/invoice-pay/{id}", name="invoice_pay")
     * @param EntityManagerInterface $entityManager
     * @param Sold $sold
     * @return Response
     */
    public function choseOnDeliveryMethod(Sold $sold, EntityManagerInterface $entityManager)
    {
        if ($sold === null) {
            return $this->redirectToRoute('advertisement_index');
        }

        $invoice = new OnDeliveryTransaction();
        $invoice->setSoldProduct($sold);
        $invoice->setUser($this->getUser());
        $invoice->setConfirmed(0);
        $invoice->onPrePersist();
        $sold->setConfirmed(true);
        $sold->setPaymentMethod('Invoice');
        $entityManager->persist($invoice);
        $entityManager->persist($sold);
        $entityManager->flush();

        self:$this->createDomPdf($sold, $invoice);

        $this->addFlash('success', 'Invoice successfully chosen!');
        return $this->redirectToRoute('my_items');
    }

    /**
     * @param Sold $sold
     * @param OnDeliveryTransaction $invoice
     */
    public function createDomPdf(Sold $sold, OnDeliveryTransaction $invoice)
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

        $domPdf->loadHtml($html);

        $domPdf->setPaper('A4', 'portrait');

        $domPdf->render();

        $output = $domPdf->output();

        $publicDirectory = '../public/invoice/';

        $pdfFilepath =  $publicDirectory . $pdfName;

        file_put_contents($pdfFilepath, $output);
    }
}
