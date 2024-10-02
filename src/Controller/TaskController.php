<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\Status;
use App\Entity\Task;
use App\Form\TaskType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TaskController extends AbstractController
{
    #[Route('/task/create/{idStatus}/{idProject}', name: 'create_task')]
    public function index(
        Request $request,
        int $idStatus,
        int $idProject,
        EntityManagerInterface $entityManager
    ): Response {
        $status = $entityManager->getRepository(Status::class)->find($idStatus);
        $project = $entityManager->getRepository(Project::class)->find($idProject);
        if (!$status || !$project) {
            throw $this->createNotFoundException('Status or Project not found');
        }
        $form = $this->createForm(TaskType::class, null, [
            'status' => $status,
            'users' => $project->getUsers()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $task->setProject($project);
            $task->setStatus($status);
            $entityManager->persist($task);
            $entityManager->flush();
            return $this->redirectToRoute('project_detail', ['id' => $idProject]);
        }
        return $this->render('task/form.html.twig', [
            'controller_name' => 'TaskController',
            'titlePage' => 'Créer une tâche',
            'form' => $form
        ]);
    }

    #[Route('/task/edit/{id}', name: 'edit_task')]
    public function editTask(Task $task, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }
        $form = $this->createForm(TaskType::class, $task, [
            'status' => $task->getStatus(),
            'users' => $task->getProject()->getUsers()
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $task = $form->getData();
            $entityManager->persist($task);
            $entityManager->flush();
            return $this->redirectToRoute('project_detail', ['id' => $task->getProject()->getId()]);
        }
        return $this->render('task/form.html.twig', [
            'controller_name' => 'TaskController',
            'titlePage' => $task->getTitle(),
            'form' => $form,
            'task' => $task
        ]);
    }

    #[Route('/task/delete/{id}', name: 'delete_task')]
    public function deleteTask(Task $task, EntityManagerInterface $entityManager): Response
    {
        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }
        $idProject = $task->getProject()->getId();
        $entityManager->remove($task);
        $entityManager->flush();
        return $this->redirectToRoute('project_detail', ['id' => $idProject]);
    }

}
