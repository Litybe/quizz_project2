<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Quizz;
use App\Entity\Tag;
use App\Repository\TagRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Validator\Constraints\NotBlank;

class CourseForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre du cours',
                'constraints' => [
                    new NotBlank(['message' => 'Le titre ne peut pas être vide'])
                ]
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false
            ])
            ->add('youtubeVideoId', TextType::class, [
                'label' => 'ID de la vidéo YouTube',
                'constraints' => [
                    new NotBlank(['message' => 'L\'ID de la vidéo YouTube est requis'])
                ]
            ])
            ->add('tags', EntityType::class, [
                'label' => 'Tags (Catégories)',
                'class' => Tag::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true, // Affiche comme des cases à cocher
                'by_reference' => false, // Important pour la relation ManyToMany
                'query_builder' => function (TagRepository $repository) {
                    return $repository->createQueryBuilder('t')
                        ->orderBy('t.name', 'ASC');
                }
            ])
            ->add('quiz', EntityType::class, [
                'label' => 'Quiz associé',
                'class' => Quizz::class,
                'choice_label' => 'name', // Assurez-vous que l'entité Quizz a une propriété 'name'
                'placeholder' => 'Sélectionnez un quiz',
                'constraints' => [
                    new NotBlank(['message' => 'Vous devez associer un quiz'])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
