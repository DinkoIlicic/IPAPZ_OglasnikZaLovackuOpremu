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
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Date;

class CouponCodesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', IntegerType::class, [
                'label' => 'Insert number of codes to generate: ',
                'required' => true,
                'data' => 1
            ])
            ->add('all', CheckboxType::class, [
                'label'    => 'All products',
                'required' => false,
            ])
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Choose category'
            ])
            ->add('product', EntityType::class,[
                'class' => Product::class,
                'choice_label' => 'name',
                'required' => false,
                'placeholder' => 'Choose product'
            ])
            ->add('dateEnabled', CheckboxType::class, [
                'label'    => 'Enable dates',
                'required' => false,
            ])
            ->add('startDate', DateTimeType::class, [
                'label' => 'Starts On: ',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ]
            ])
            ->add('expireDate', DateTimeType::class, [
                'label' => 'Ends On: ',
                'placeholder' => [
                    'year' => 'Year', 'month' => 'Month', 'day' => 'Day',
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
        $resolver->setDefaults([
            'validation_groups' => false
        ]);
    }
}