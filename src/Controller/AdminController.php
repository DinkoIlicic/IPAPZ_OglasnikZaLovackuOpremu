<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 19.02.19.
 * Time: 11:11
 */

namespace App\Controller;

use App\Entity\Seller;
use App\Entity\User;
use App\Form\PasswordFormType;
use App\Form\ProfileFormType;
use App\Repository\SellerRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route; //@codingStandardsIgnoreLine
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminController extends AbstractController
{
    /**
     * @Route("/admin/", name="admin_index")
     * @return           \Symfony\Component\HttpFoundation\Response
     */
    public function index()
    {
        return $this->render(
            'admin/index.html.twig'
        );
    }

    /**
     * @Route("/admin/appliers", name="check_apply_for_seller")
     * @param                    SellerRepository $sellerRepository
     * @return                   \Symfony\Component\HttpFoundation\Response
     */
    public function listAllAppliersForSeller(SellerRepository $sellerRepository)
    {
        $sellers = $sellerRepository->findBy(
            [],
            [
                'id' => 'DESC'
            ]
        );
        return $this->render(
            '/admin/list_of_all_appliers.html.twig',
            [
                'sellers' => $sellers
            ]
        );
    }

    /**
     * @Route("/admin/applier/{id}", name="check_one_applier_for_seller")
     * @param                        SellerRepository $sellerRepository
     * @param                        Seller $seller
     * @return                       \Symfony\Component\HttpFoundation\Response
     */
    public function listOneApplierForSeller(Seller $seller, SellerRepository $sellerRepository)
    {
        $sell = $sellerRepository->findOneBy(
            [
                'id' => $seller->getId()
            ]
        );
        return $this->render(
            '/admin/view_applier.html.twig',
            [
                'seller' => $sell,
                'verified' => $sell->getVerified()
            ]
        );
    }

    /**
     * @Route("/admin/verify-applier/{id}", name="verify_applier")
     * @param                               Seller $seller
     * @param                               EntityManagerInterface $entityManager
     * @return                              \Symfony\Component\HttpFoundation\Response
     */
    public function verifyApplier(Seller $seller, EntityManagerInterface $entityManager)
    {
        if ($seller->getVerified() === 0) {
            $seller->setVerified(1);
            $seller->getUser()->setRoles(['ROLE_SELLER']);
            $this->addFlash('success', 'Applier verified!');
        } elseif ($seller->getVerified() === 1) {
            $seller->setVerified(0);
            $seller->getUser()->setRoles(['ROLE_USER']);
            $this->addFlash('success', 'Applier unverified!');
        }

        $entityManager->flush();
        return $this->redirectToRoute(
            'check_one_applier_for_seller',
            [
                'id' => $seller->getId()
            ]
        );
    }

    /**
     * @Route("/admin/users", name="list_of_users")
     * @param                 UserRepository $userRepository
     * @return                \Symfony\Component\HttpFoundation\Response
     */
    public function listOfUsers(UserRepository $userRepository)
    {
        $listOfUsers = $userRepository->findBy(
            [],
            [
                'fullName' => 'ASC'
            ]
        );
        return $this->render(
            '/admin/view_users.html.twig',
            [
                'users' => $listOfUsers
            ]
        );
    }

    /**
     * @Route("/admin/user-info/{id}", name="new_user_info_admin")
     * @param                          EntityManagerInterface $entityManager
     * @param                          Request $request
     * @param                          User $user
     * @return                         \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserInfoAdmin(
        User $user,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(ProfileFormType::class, $user);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $user->setFullName($user->getFirstName() . ' ' . $user->getLastName());
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'User info updated!');
            return $this->redirectToRoute('list_of_users');
        }

        return $this->render(
            '/admin/update_user_info.html.twig',
            [
                'profileForm' => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/admin/user-new-password/{id}", name="new_password_admin")
     * @param                                  UserPasswordEncoderInterface $passwordEncoder
     * @param                                  EntityManagerInterface $entityManager
     * @param                                  Request $request
     * @param                                  User $user
     * @return                                 \Symfony\Component\HttpFoundation\Response
     */
    public function updateUserPasswordAdmin(
        User $user,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager
    ) {
        $form = $this->createForm(PasswordFormType::class, $user);
        $form->handleRequest($request);
        if ($this->isGranted('ROLE_ADMIN') && $form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash('success', 'Password updated!');
            return $this->redirectToRoute('list_of_users');
        }

        return $this->render(
            '/admin/update_user_password.html.twig',
            [
                'profileForm' => $form->createView(),
            ]
        );
    }
}
