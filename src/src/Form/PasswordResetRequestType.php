<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Form type for requesting a password reset.
 */
class PasswordResetRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label'      => 'Email address:*',
                'constraints' => [
                    new Assert\NotBlank(message: 'Please enter your email'),
                    new Assert\Email(message: 'Please enter a valid email address'),
                ],
                'attr'       => ['autocomplete' => 'email', 'placeholder' => 'your@email.com', 'class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
        ;
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
        return 'password_reset_request';
    }
}
