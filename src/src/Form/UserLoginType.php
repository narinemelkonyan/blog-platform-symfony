<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserLoginType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'Email address:*',
                'attr'  => [
                    'autocomplete' => 'email',
                    'placeholder'  => 'your@email.com',
                    'class'        => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('password', PasswordType::class, [
                'label' => 'Password:*',
                'attr'  => [
                    'autocomplete' => 'current-password',
                    'placeholder'  => 'Password',
                    'class'        => 'form-control',
                ],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('remember_me', CheckboxType::class, [
                'label'      => 'Remember me',
                'required'   => false,
                'mapped'     => false,
                'attr'       => ['class' => 'form-check-input'],
                'label_attr' => ['class' => 'form-check-label'],
            ])
        ;
    }

    /**
     * Enables explicit CSRF protection with a unique token ID.
     * Browser-side validation is disabled in favour of server-side Symfony constraints.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => true,
            'csrf_token_id'   => 'user_login',
            'attr'            => ['novalidate' => 'novalidate'],
        ]);
    }

    /**
     * Returns a unique block prefix for clean HTML field names.
     */
    public function getBlockPrefix(): string
    {
        return 'user_login';
    }
}
