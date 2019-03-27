<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/27/19
 * Time: 1:25 PM
 */

namespace App\Form;

use App\Entity\PaymentMethod;
use App\Entity\UserAddress;
use App\Repository\PaymentMethodRepository;
use App\Repository\UserAddressRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\NotBlank;

class PaymentOptionFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var \App\Entity\User $user
         */
        $user = $options['user'];
        $id = $user->getId();

        $builder
            ->add(
                'address',
                EntityType::class,
                [
                    'class' => UserAddress::class,
                    'label' => 'Address: ',
                    'query_builder' => function (UserAddressRepository $er) use ($id) {
                        return $er->createQueryBuilder('ua')
                            ->WHERE('ua.user = :id')
                            ->setParameter('id', $id);
                    },
                    'choice_label' => function ($userAddress) {
                        /**
                         * @var \App\Entity\UserAddress $userAddress
                         */
                        return $userAddress->getAddress1() . ', ' .
                            $userAddress->getCity() . ', ' .
                            $userAddress->getCountry();
                    },
                    'constraints' => array(
                        new NotBlank(),
                    )

                ]
            )
            ->add(
                'payment',
                EntityType::class,
                [
                    'class' => PaymentMethod::class,
                    'label' => 'Payment option: ',
                    'query_builder' => function (PaymentMethodRepository $er) {
                        return $er->createQueryBuilder('p')
                        ->WHERE('p.enabled = 1');
                    },
                    'choice_label' => function ($option) {
                        /**
                         * @var \App\Entity\PaymentMethod $option
                         */
                        return $option->getMethod();
                    }
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => null,
            ]
        );
        $resolver->setRequired(
            array(
                'user',
            )
        );
    }
}
