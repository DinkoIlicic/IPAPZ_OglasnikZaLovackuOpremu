<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/21/19
 * Time: 2:26 PM
 */

namespace App\Form;

use App\Entity\PaymentMethod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaymentMethodFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'method',
                TextType::class,
                [
                    'label' => 'Insert Your Payment Method Name here: '
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => PaymentMethod::class,
            ]
        );
    }
}
