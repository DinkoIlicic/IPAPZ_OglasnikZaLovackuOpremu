<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/26/19
 * Time: 1:00 PM
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class ShippingFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'query',
                TextType::class,
                [
                    'attr' => [
                        'placeholder' => 'Enter here'
                    ],
                    'label' => 'Country name: ',
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            )
            ->add(
                'price',
                MoneyType::class,
                [
                    'label' => 'Shipping price:  ',
                    'constraints' =>
                        [
                            new NotBlank(),
                            new GreaterThanOrEqual(['value' => 0]),
                        ],
                    'invalid_message' => 'Only numbers and dot allowed!',
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
    }
}
