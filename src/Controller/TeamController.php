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
    public function editMember(User $user, EntityManagerInterface $entityUserManager,Request $request): Response
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

    #[Route('/team/member/detail/{id}', name: 'detailMember')]
    public function detailMember(UserRepository $userRepository): Response
    {
        if(!$user){
            throw $this->createNotFoundException('User not found');
        }
        $team = $userRepository->find();
        return $this->render('team/index.html.twig', [
            'controller_name' => 'TeamController',
            'titlePage' => 'Équipe',
            'team' => $team
        ]);
    }

    #[Route('/team/member/delete/{id}', name: 'deleteMember')]
    public function deleteMember(int $id, EntityManagerInterface $entityUserManager): Response
    {
        if(!$user){
            throw $this->createNotFoundException('User not found');
        }
        $repository = $entityUserManager->getRepository(User::class);
        $user = $repository->find($id);
        $entityUserManager->remove($user);
        $entityUserManager->flush();
        return $this->redirectToRoute('team');
    }

}
