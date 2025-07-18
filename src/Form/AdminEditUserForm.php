<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
class AdminEditUserForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('Pseudo', TextType::class, [
                'label' => 'Pseudo',
                'required' => true,
            ])
            ->add('Email', EmailType::class, [
                'label' => 'Adresse Email',
                'required' => true,
            ])
            ->add('Roles', ChoiceType::class, [
                'label' => 'Rôles',
                'multiple' => true,
                'expanded' => true, // Affiche comme des cases à cocher
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Premium' => 'ROLE_PREMIUM',
                    'Modérateur' => 'ROLE_MODO',
                    'Administrateur' => 'ROLE_ADMIN',
                    // Ajoutez d'autres rôles si nécessaire
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr' => ['class' => 'btn btn-primary']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}