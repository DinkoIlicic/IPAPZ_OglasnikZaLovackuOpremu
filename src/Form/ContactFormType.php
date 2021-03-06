<?php
/**
 * Created by PhpStorm.
 * User: dinko
 * Date: 25.02.19.
 * Time: 13:13
 */

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'name',
                TextType::class,
                [
                    'label' => 'Name: ',
                    'required' => true,
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            )
            ->add(
                'from',
                EmailType::class,
                [
                    'label' => 'Your email: ',
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => 'Message: ',
                    'constraints' =>
                        [
                            new NotBlank(),
                        ]
                ]
            );
    }
}
