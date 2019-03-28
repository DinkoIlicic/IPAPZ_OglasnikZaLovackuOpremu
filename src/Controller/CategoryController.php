<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 11:09 AM
 */

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryFormType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine

class CategoryController extends AbstractController
{

    /**
     * @Route("/admin/categories", name="list_of_categories")
     * @param                      Request $request
     * @param                      EntityManagerInterface $entityManager
     * @param                      CategoryRepository $categoryRepository
     * @return                     \Symfony\Component\HttpFoundation\Response
     */
    public function listOfAllCategories(
        Request $request,
        EntityManagerInterface $entityManager,
        CategoryRepository $categoryRepository
    ) {
        $allCategories = $categoryRepository->findBy(
            [],
            [
                'name' => 'ASC'
            ]
        );
        $form = $this->createForm(CategoryFormType::class);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            /**
             * @var Category $category
             */
            $category = $form->getData();
            $category->setUser($this->getUser());
            $category->setVisibility(true);
            $category->setVisibilityAdmin(true);
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'New category added!');
            return $this->redirectToRoute('list_of_categories');
        }

        return $this->render(
            '/admin/category_list.html.twig',
            [
                'form' => $form->createView(),
                'message' => '',
                'categories' => $allCategories
            ]
        );
    }

    /**
     * @Route("/admin/update-category/{id}", name="check_one_category")
     * @param                                EntityManagerInterface $entityManager
     * @param                                Request $request
     * @param                                Category $category
     * @return                               \Symfony\Component\HttpFoundation\Response
     */
    public function updateOneCategory(
        Category $category,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(CategoryFormType::class, $category);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'Category updated!');
            return $this->redirectToRoute('list_of_categories');
        }

        return $this->render(
            '/admin/view_category.html.twig',
            [
                'form' => $form->createView(),
                'category' => $category,
            ]
        );
    }

    /**
     * @Route("/admin/cat-visibility/{id}", name="category_visibility_admin")
     * @param                               EntityManagerInterface $entityManager
     * @param                               Category $category
     * @return                              \Symfony\Component\HttpFoundation\Response
     */
    public function updateCategoryVisibilityAdmin(
        Category $category,
        EntityManagerInterface $entityManager
    ) {
        if ($category->getVisibilityAdmin() === false) {
            $category->setVisibilityAdmin(true);
            $this->addFlash('success', 'Category made visible!');
        } elseif ($category->getVisibilityAdmin() === true) {
            $category->setVisibilityAdmin(false);
            $this->addFlash('success', 'Category hidden!');
        } else {
            $this->addFlash('warning', 'Something went wrong!');
        }

        $entityManager->flush();
        return $this->redirectToRoute('list_of_categories');
    }
}
