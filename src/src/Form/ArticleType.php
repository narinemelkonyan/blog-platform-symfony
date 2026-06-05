<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class ArticleType extends AbstractType
{
    public function __construct(private readonly string $tinyMceApiKey)
    {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $article = $options['data'] ?? null;
        $isNew = $article->getId() === null;
        $coverRequired = $isNew;
        $builder
            ->add('title', TextType::class, [
                'label' => 'Article Title',
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Content',
                'attr'  => [
                    'data-controller'            => 'tinymce',
                    'data-tinymce-api-key-value' =>  $this->tinyMceApiKey,
                    ]
                ])
            ->add('coverImage', FileType::class, [
                'label'    => 'Cover Image',
                'mapped'   => false,
                'required'    => $coverRequired,
                'constraints' => [
                    ...($coverRequired ? [new NotNull(message: 'Cover image is required.')] : []),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }

    /**
     * Returns a unique block prefix for clean HTML field names.
     */
    public function getBlockPrefix(): string
    {
        return 'article_form';
    }
}
