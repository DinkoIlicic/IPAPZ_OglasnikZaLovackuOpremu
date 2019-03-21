<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 22.02.19.
 * Time: 19:02
 */

namespace App\Form;

use App\Entity\Product;
use App\Entity\Sold;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListOfBoughtItemsPerProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /**
         * @var User $user
         */
        $user = $options['user'];
        $id = $user->getId();
        $builder
            ->add(
                'product',
                EntityType::class,
                [
                    'class' => Product::class,
                    'query_builder' => function (EntityRepository $er) use ($id) {
                        return $er->createQueryBuilder('u')
                            ->WHERE('u.user = :id')
                            ->setParameter('id', $id);
                    },
                    'choice_label' => function ($product) {
                        /**
                         * @var Product $product
                         */
                        return $product->getName();
                    },
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
        $resolver->setRequired(
            array(
                'user',
            )
        );
    }
}
