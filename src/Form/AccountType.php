<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $user = $options['user'];

        $builder
            ->add('lastname', null, ['label' => 'Nom'])
            ->add('firstname', null, ['label' => 'Prénom'])
            ->add('email', null, ['label' => 'Email'])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Les mots de passe doivent être identiques.',
                'first_options' => ['label' => 'Mot de passe'],
                'second_options' => ['label' => 'Répéter le mot de passe'],
                'required' => false,
                'mapped' => false,
            ])
            ->add('googleAuth', CheckboxType::class, [
                'label' => 'Activer Google Auth',
                'required' => false,
                'mapped' => false,
                'data' => !empty($user->getGoogleAuthenticatorSecret()) // Initialiser la checkbox
            ])
            ->add('emailAuth', CheckboxType::class, [
                'label' => 'Activer la double authentification par mail',
                'required' => false,
                'mapped' => false,
                'data' => !empty($user->isEmailAuthEnabled()) // Initialiser la checkbox
            ])
            ->add('Enregistrer', SubmitType::class, ['attr' => ['class' => 'button button-submit']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'user' => null,
        ]);
    }
}
