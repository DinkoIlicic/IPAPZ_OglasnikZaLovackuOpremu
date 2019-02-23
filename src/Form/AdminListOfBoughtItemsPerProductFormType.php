<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 23.02.19.
 * Time: 12:30
 */

namespace App\Form;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Product;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AdminListOfBoughtItemsPerProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => function($product) {
                    /** @var Product $product */
                    return $product->getName();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Sold::class,
        ]);
    }
}