<?php

namespace App\Form;

use App\Entity\Comment;
use App\Validator\NoForbiddenWords;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('content', TextareaType::class, [
                'label'       => 'Comment',
                'constraints' => [
                    new NotBlank(message: 'Comment cannot be empty.'),
                    new Length(
                        min: 2,
                        max: 1000,
                        minMessage: 'Comment must be at least {{ limit }} characters.',
                        maxMessage: 'Comment cannot exceed {{ limit }} characters.',
                    ),
                    new NoForbiddenWords(),
                ],
                'attr' => [
                    'rows'        => 4,
                    'placeholder' => 'Write your comment...',
                ],
            ])
            ->add('phone', HiddenType::class, [
                'mapped'      => false,
                'required'    => false,
                'constraints' => [
                    new Blank(message: 'Bot detected.'),
                ],
                'attr' => ['autocomplete' => 'off'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
            'attr'       => ['novalidate' => 'novalidate'],
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'comment_form';
    }
}
