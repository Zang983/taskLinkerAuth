<?php

namespace App\Controller;

use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

## TODO : Gérer la création/édition et suppression de projets / utilisateurs / tâches (suppression également pour ce dernier)
## TODO : Faire un dernier check pour voir si tout est OK

class ProjectController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();
        return $this->render('project/index.html.twig', [
            'controller_name' => 'ProjectController',
            'titlePage' => 'Projets',
            'projects' => $projects,
        ]);
    }

    #[Route('/project/create', name: 'create_project')]
    public function createProject(Request $request, EntityManagerInterface $entityProjectManager): Response
    {
        $form = $this->createForm(ProjectType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $project = $form->getData();
            $entityProjectManager->persist($project);
            $entityProjectManager->flush();
            return $this->redirectToRoute('home');
        }
        return $this->render('project/form.html.twig', [
            'controller_name' => 'CreateProjectController',
            'form' => $form,
            'titleBlock' => 'Nouveau projet'
        ]);
    }

    #[Route('/project/delete/{id}', name: 'delete_project')]
    public function deleteProject(EntityManagerInterface $entityProjectManager, Project $project): Response
    {
        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }
        $entityProjectManager->remove($project);
        $entityProjectManager->flush();
        return $this->redirectToRoute('home');
    }

    #[Route('/project/edit/{id}', name: 'edit_project')]
    public function editProject(
        Project $project,
        EntityManagerInterface $entityProjectManager,
        Request $request
    ): Response {
        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $project = $form->getData();
            $entityProjectManager->persist($project);
            $entityProjectManager->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('project/form.html.twig', [
            'controller_name' => 'EditProjectController',
            'titlePage' => $project->getName(),
            'titleBlock' => $project->getName(),
            'project' => $project,
            'form' => $form
        ]);
    }

    #[Route('/project/detail/{id}', name: 'project_detail')]
    public function project(Project $project): Response
    {
        if (!$project) {
            throw $this->createNotFoundException('Project not found');
        }
        $projectTeam = $project->getUsers();
        $tasks = $project->getTasks();

        $tasksByStatus = [
        ];

        foreach ($tasks as $task) {
            $idStatus = $task->getStatus()->getId();
            if (!isset($tasksByStatus[$idStatus])) {
                $tasksByStatus[$idStatus] = [
                    "idStatus" => $idStatus,
                    "libelleStatus" => $task->getStatus()->getLibelle(),
                    "tasks" => []
                ];
            }
            $tasksByStatus[$idStatus]["tasks"][] = $task;
        }
        usort($tasksByStatus, function ($a, $b) {
            return $a['idStatus'] <=> $b['idStatus'];
        });
        return $this->render('project/detail.html.twig', [
            'controller_name' => 'DetailProjectController',
            'project' => $project,
            'titlePage' => $project->getName(),
            'tasksByStatus' => $tasksByStatus,
            'projectTeam' => $projectTeam,
        ]);
    }

}