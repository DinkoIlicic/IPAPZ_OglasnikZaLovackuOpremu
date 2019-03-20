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
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponCodesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', IntegerType::class, [
                'label' => 'Insert number of codes to generate: '
            ])
            ->add('discount', TextType::class, [
                'label' => 'Insert Your discount here: '
            ])
            ->add('all', CheckboxType::class, [
                'label'    => 'All products?',
                'required' => false,
            ])
            ->add('category', EntityType::class,[
                'class' => Category::class,
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('product', EntityType::class,[
                'class' => Product::class,
                'choice_label' => 'name',
                'required' => false,
            ])
            ->add('dateEnabled', CheckboxType::class, [
                'label'    => 'Enable dates?',
                'required' => false,
            ])
            ->add('startDate', DateType::class, [
                'date_label' => 'Starts On: ',
                'widget' => 'choice',
            ])
            ->add('expireDate', DateType::class, [
                'date_label' => 'Ends On: ',
                'widget' => 'choice',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}