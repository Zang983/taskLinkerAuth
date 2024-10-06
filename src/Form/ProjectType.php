<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', null, [
                'label' => 'Titre du projet',
            ])
            ->add('users', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'fullName',
                'multiple' => true,
                'label'=>'Inviter des membres',
                'by_reference' => false,
            ]
            )
            ->add('Continuer', SubmitType::class, [
                'attr'=>['class'=>'button button-submit']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Project::class,
        ]);
    }
}
