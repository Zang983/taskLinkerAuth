<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\AccountType;
use Doctrine\ORM\EntityManagerInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Label\LabelAlignment;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{

    private $googleAuthenticator;
    private $projectDir;

    public function __construct(GoogleAuthenticatorInterface $googleAuthenticator, KernelInterface $kernel)
    {
        $this->projectDir = $kernel->getProjectDir();
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
        $form = $this->createForm(AccountType::class, $user, ['user' => $user]);
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
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors de la mise à jour de vos informations');
            }
            if ($user->isGoogleAuthenticatorEnabled()) {
                $this->createQrCodesPNG($this->googleAuthenticator->getQRContent($user), $user);
            }
            return $this->redirectToRoute('app_account');
        }
        return $this->render('security/account.html.twig', [
            'form' => $form->createView(),
            'QrCode' => $user->isGoogleAuthenticatorEnabled() ? '/qrCodes/' . $user->getId() . '.png' : null
        ]);
    }

    #[Route(path: '/getGoogleAuth', name: 'app_google_auth')]
    public function getGoogleAuth()
    {
        return $this->render('security/google_auth.html.twig');
    }

    private function createQrCodesPNG(string $qrCodeContent, User $user)
    {
        $builder = new Builder(
            writer: new PngWriter(),
            writerOptions: [],
            validateResult: false,
            data: $qrCodeContent,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 400,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            labelText: 'Scan the code',
            labelAlignment: LabelAlignment::Center
        );
        try {
            $qrCode = $builder->build();
            $filePath = $this->projectDir . '/public/qrCodes/' . $user->getId() . '.png';
            $qrCode->saveToFile($filePath);
        } catch (\Exception $e) {
            dd($e);
        }
    }

}
