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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProductInfoFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Insert Your Product Name here: '
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Insert Your Product Price here: '
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Insert Your Additional Information about Product here: ',
                'required'  => false,
                'empty_data' => '',
            ])
            ->add('categories', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('availableQuantity', IntegerType::class,[
                'label' => 'Available Quantity:'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }

}