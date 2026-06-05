<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class UserRegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label'      => 'First Name:*',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('lastName', TextType::class, [
                'label'      => 'Last Name:*',
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('email', EmailType::class, [
                'label'      => 'Email address:*',
                'attr'       => ['autocomplete' => 'email', 'placeholder' => 'your@email.com', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('plainPassword', RepeatedType::class, [
                'mapped'          => true,
                'type'            => PasswordType::class,
                'first_options'   => [
                    'label' => 'Password:*',
                    'attr'  => [
                        'autocomplete' => 'new-password',
                        'class'        => 'form-control',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                    'constraints' => [
                        new Assert\NotBlank(message: 'Please enter a password'),
                        new Assert\Length(
                            min: 8,
                            max: 72,
                            minMessage: 'Password must be at least {{ limit }} characters',
                            maxMessage: 'Password must not exceed {{ limit }} characters',
                        ),
                    ],
                ],
                'second_options'  => [
                    'label' => 'Confirm Password:*',
                    'attr'  => [
                        'autocomplete' => 'new-password',
                        'class'        => 'form-control',
                    ],
                    'label_attr' => ['class' => 'form-label'],
                ],
                'invalid_message' => 'Passwords do not match',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'validation_groups' => ['Default', 'registration'],
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }

    /**
     * Returns a unique block prefix for clean HTML field names.
     */
    public function getBlockPrefix(): string
    {
        return 'user_register';
    }
}
