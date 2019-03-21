<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/20/19
 * Time: 11:49 AM
 */

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponCodesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'amount',
                IntegerType::class,
                [
                    'label' => 'Insert number of codes to generate: ',
                    'required' => true,
                    'data' => 1
                ]
            )
            ->add(
                'allProducts',
                CheckboxType::class,
                [
                    'label' => 'All products',
                    'required' => false,
                ]
            )
            ->add(
                'category',
                EntityType::class,
                [
                    'class' => Category::class,
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => 'Choose category'
                ]
            )
            ->add(
                'product',
                EntityType::class,
                [
                    'class' => Product::class,
                    'choice_label' => 'name',
                    'required' => false,
                    'placeholder' => 'Choose product'
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
        $resolver->setDefaults(
            [
                'validation_groups' => false
            ]
        );
    }
}
