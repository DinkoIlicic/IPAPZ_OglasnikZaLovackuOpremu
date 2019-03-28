<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/20/19
 * Time: 9:53 AM
 */

namespace App\Form;

use App\Entity\Coupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class CouponFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'codeGroupName',
                TextType::class,
                [
                    'label' => 'Insert Your Group Code Name here: ',
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            )
            ->add(
                'discount',
                TextType::class,
                [
                    'label' => 'Insert Your discount here: ',
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Coupon::class,
            ]
        );
    }
}
