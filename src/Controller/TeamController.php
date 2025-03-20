<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_MANAGER')]
class TeamController extends AbstractController
{
    #[Route('/team', name: 'team')]
    public function index(UserRepository $userRepository): Response
    {
        $team = $userRepository->findAll();
        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'titlePage' => 'Équipe',
            'team' => $team
        ]);

    }
    #[Route('/team/edit/{id}', name: 'edit_member')]
    public function editMember(EntityManagerInterface $entityUserManager,Request $request, User $user = null): Response
    {
        if(!$user){
            throw $this->createNotFoundException('User not found');
        }
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()){
            $entityUserManager->flush();
            return $this->redirectToRoute('team');
        }

        return $this->render('/team/form.html.twig', [
            'controller_name' => 'EditMemberController',
            'titlePage' => $user->getFullName(),
            'titleBlock' => 'Modifier un membre',
            'user' => $user,
            'form' => $form
        ]);
    }
    #[Route('/team/member/delete/{id}', name: 'deleteMember')]
    public function deleteMember(EntityManagerInterface $entityUserManager, User $user = null): Response
    {
        if(!$user){
            throw $this->createNotFoundException('User not found');
        }
        $entityUserManager->remove($user);
        $entityUserManager->flush();
        return $this->redirectToRoute('team');
    }

}
