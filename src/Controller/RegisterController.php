<?php

namespace App\Controller;


use App\Form\RegisterType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RegisterController extends AbstractController
{
    #[Route(path: '/signIn', name: 'app_register')]
    public function home(Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegisterType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $user->setContractType('CDI');
            $user->setEmployementDate(new \DateTime());
            $entityManager->persist($user);
            $entityManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render(
            'signinAndRegister/register.html.twig',
            [
                'form' => $form
            ]
        );
    }

}
