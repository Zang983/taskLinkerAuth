<?php

namespace App\Controller;

use App\Form\AccountType;
use Doctrine\ORM\EntityManagerInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    private $googleAuthenticator;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator)
    {
        $this->googleAuthenticator = $googleAuthenticator;
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException(
            'This method can be blank - it will be intercepted by the logout key on your firewall.'
        );
    }

    #[Route(path: '/account', name: 'app_account')]
    public function account(
        Request $request,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $entityManager
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(AccountType::class, $user,['user'=>$user]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($form['password']->getData()) {
                $user->setPassword($hasher->hashPassword($user, $form['password']->getData()));
            }
            $form['googleAuth']->getData() === true ? $user->setGoogleAuthenticatorSecret(
                $this->googleAuthenticator->generateSecret()
            ) : $user->setGoogleAuthenticatorSecret(null);
            try {
                $entityManager->flush();
                $this->addFlash(
                    'success',
                    'Vos informations ont bien été mises à jour, votre clé est : ' . $this->googleAuthenticator->getQRContent(
                        $user
                    )
                );
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de vos informations');
            }
        }
        return $this->render('security/account.html.twig', [
            'form' => $form->createView(),
            'QrCode' => $this->googleAuthenticator->getQRContent($user)
        ]);
    }

    #[Route(path: '/getGoogleAuth', name: 'app_google_auth')]
    public function getGoogleAuth()
    {
        return $this->render('security/google_auth.html.twig');
    }

}
