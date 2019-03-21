<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 20.02.19.
 * Time: 13:44
 */

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;

class ProductInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Insert Your Product Name here: '
                ]
            )
            ->add(
                'price',
                MoneyType::class,
                [
                    'label' => 'Insert Your Product Price here: '
                ]
            )
            ->add(
                'content',
                TextareaType::class,
                [
                    'label' => 'Insert Your Additional Information about Product here: ',
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add(
                'productCategory',
                EntityType::class,
                [
                    'class' => Category::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'constraints' => array(
                        new Count(
                            array(
                                'min' => 1,
                                'minMessage' => "Please, choose one or more categories"
                            )
                        )
                    )
                ]
            )
            ->add(
                'customUrl',
                TextType::class,
                [
                    'label' => 'Page name:',
                    'constraints' => [
                        new Length(
                            [
                                'max' => 255,
                                'maxMessage' => 'Page name can not be longer than 255 characters'
                            ]
                        )
                    ]
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'data_class' => Product::class,
            ]
        );
    }
}
