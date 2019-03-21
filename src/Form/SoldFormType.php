<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 21.02.19.
 * Time: 11:42
 */

namespace App\Form;

use App\Entity\Sold;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SoldFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'quantity',
                IntegerType::class,
                [
                    'label' => 'Quantity: ',
                    'data' => 1
                ]
            )
            ->add(
                'couponCodeName',
                TextType::class,
                [
                    'label' => 'Coupon Code: '
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Sold::class,
            ]
        );
    }
}
