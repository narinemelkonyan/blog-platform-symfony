<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProfileType extends AbstractType
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
                'attr'       => ['class' => 'form-control'],
                'label_attr' => ['class' => 'form-label'],
            ])
            ->add('avatar', FileType::class, [
                'label'       => 'Avatar',
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        maxSizeMessage: 'The image is too large. Maximum size is 5MB.',
                        mimeTypesMessage: 'Please upload a valid image (JPEG, PNG, WebP).',
                    ),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }

    /**
     * Returns a unique block prefix for clean HTML field names.
     */
    public function getBlockPrefix(): string
    {
        return 'user_profile';
    }
}
