<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 22.02.19.
 * Time: 17:17
 */

namespace App\Form;

use App\Entity\Sold;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ListOfUserBoughtItemsFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('user', EntityType::class, [
                'class' => User::class,
                'choice_label' => function($user) {
                    /** @var User $user */
                    return $user->getFirstName() . ' ' . $user->getLastName();
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