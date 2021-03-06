<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/21/19
 * Time: 9:57 AM
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class RemoveCouponCodesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'startId',
                IntegerType::class,
                [
                    'label' => 'Starting id: ',
                    'required' => true,
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            )
            ->add(
                'endId',
                IntegerType::class,
                [
                    'label' => 'Ending id: ',
                    'required' => true,
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
                'data_class' => null,
            ]
        );
    }
}
