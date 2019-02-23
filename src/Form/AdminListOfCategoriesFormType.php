<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 23.02.19.
 * Time: 12:35
 */

namespace App\Form;
use App\Entity\Sold;
use App\Entity\User;
use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\ORM\EntityRepository;

class AdminListOfCategoriesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', EntityType::class, [
                'class' => Category::class,
                'label' => 'Category name',
                'choice_label' => function($id) {
                    /** @var Category $id */
                    return $id->getName();
                },
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}