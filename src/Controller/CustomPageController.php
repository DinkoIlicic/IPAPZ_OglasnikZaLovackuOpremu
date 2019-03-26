<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 10:12 AM
 */

namespace App\Controller;

use App\Entity\CustomPage;
use App\Form\CustomPageFormType;
use App\Repository\CustomPageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class CustomPageController extends AbstractController
{
    /**
     * @Route("/admin/add-page", name="add_custom_page_admin")
     * @param                    Request $request
     * @param                    EntityManagerInterface $entityManager
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function addCustomPage(Request $request, EntityManagerInterface $entityManager)
    {
        $form = $this->createForm(CustomPageFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var $customPage CustomPage
             */
            $customPage = $form->getData();
            if ($customPage->getContent() === null) {
                $this->addFlash('warning', 'Content field can not be empty!');
                return $this->redirectToRoute('view_custom_pages_admin');
            }

            $customPage->setPageName(str_replace(' ', '-', $customPage->getPageName()));
            $customPage->setVisibilityAdmin(1);
            $entityManager->persist($customPage);
            $entityManager->flush();
            $this->addFlash('success', 'Page added!');
            return $this->redirectToRoute('view_custom_pages_admin');
        }

        return $this->render(
            '/admin/add_custom_page.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/view-pages", name="view_custom_pages_admin")
     * @param                      CustomPageRepository $customPageRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function viewCustomPages(CustomPageRepository $customPageRepository)
    {
        $allCustomPages = $customPageRepository->findBy([], ['id' => 'DESC']);
        return $this->render(
            '/admin/view_custom_pages.html.twig',
            [
                'allCustomPages' => $allCustomPages,
            ]
        );
    }

    /**
     * @Route("/admin/delete-page/{id}", name="delete_custom_page_admin")
     * @param                            CustomPage $customPage
     * @param                            EntityManagerInterface $entityManager
     * @return                           \Symfony\Component\HttpFoundation\Response
     */
    public function deleteCustomPage(EntityManagerInterface $entityManager, CustomPage $customPage)
    {
        $entityManager->remove($customPage);
        $entityManager->flush();
        $this->addFlash('success', 'Page deleted!');
        return $this->redirectToRoute('view_custom_pages_admin');
    }

    /**
     * @Route("/admin/edit-page/{id}", name="edit_custom_page_admin")
     * @param                          CustomPage $customPage
     * @param                          EntityManagerInterface $entityManager
     * @param                          Request $request
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function editCustomPage(CustomPage $customPage, EntityManagerInterface $entityManager, Request $request)
    {
        $form = $this->createForm(CustomPageFormType::class, $customPage);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var $customPage CustomPage
             */
            $customPage = $form->getData();
            $customPage->setPageName(str_replace(' ', '-', $customPage->getPageName()));
            $entityManager->persist($customPage);
            $entityManager->flush();
            $this->addFlash('success', 'Page edited!');
            return $this->redirectToRoute('view_custom_pages_admin');
        }

        return $this->render(
            '/admin/edit_custom_page.html.twig',
            [
                'form' => $form->createView()
            ]
        );
    }

    /**
     * @Route("/admin/visibility-page/{id}", name="visibility_custom_page_admin")
     * @param                                EntityManagerInterface $entityManager
     * @param                                CustomPage $customPage
     * @return                               \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function updateVisibilityCustomPage(EntityManagerInterface $entityManager, CustomPage $customPage)
    {
        if ($customPage->getVisibilityAdmin() === 0) {
            $customPage->setVisibilityAdmin(1);
            $this->addFlash('success', 'Page made visible!');
        } elseif ($customPage->getVisibilityAdmin() === 1) {
            $customPage->setVisibilityAdmin(0);
            $this->addFlash('success', 'Page hidden!');
        } else {
            $this->addFlash('warning', 'Something went wrong');
        }

        $entityManager->flush();
        return $this->redirectToRoute('view_custom_pages_admin');
    }
}
