<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotCompromisedPassword;
use Symfony\Component\Validator\Constraints\PasswordStrength;

/**
 * Form type for password reset.
 */
class PasswordResetType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type'            => PasswordType::class,
                'first_options'   => [
                    'label'      => 'New Password',
                    'attr'       => ['class' => 'form-control'],
                    'label_attr' => ['class' => 'form-label'],
                    'constraints' => [
                        new Assert\NotBlank(message: 'Please enter a password'),
                        new Assert\Length(
                            min: 8,
                            max: 72,
                            minMessage: 'Password must be at least {{ limit }} characters',
                            maxMessage: 'Password must not exceed {{ limit }} characters',
                        ),
                        new PasswordStrength(),
                        new NotCompromisedPassword(),
                    ],
                ],
                'second_options'  => [
                    'label'      => 'Confirm Password',
                    'attr'       => ['class' => 'form-control'],
                    'label_attr' => ['class' => 'form-label'],
                ],
                'invalid_message' => 'Passwords do not match',
                'mapped'          => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }

    /**
     * Returns a unique block prefix for clean HTML field names.
     */
    public function getBlockPrefix(): string
    {
        return 'password_reset';
    }
}
