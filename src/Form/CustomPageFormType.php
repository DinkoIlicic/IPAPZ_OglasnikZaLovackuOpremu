<?php
/**
 * Created by PhpStorm.
 * User: inchoo
 * Date: 3/19/19
 * Time: 1:28 PM
 */

namespace App\Form;


use App\Entity\CustomPage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomPageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('pageName', TextType::class, [
                'label' => 'Insert Your Page Name here: '
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Insert Your Content here: ',
                'attr' => array('rows' => '15'),
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomPage::class,
        ]);
    }
}