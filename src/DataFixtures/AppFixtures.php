<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Factory\ProjectFactory;
use App\Factory\StatusFactory;
use App\Factory\TaskFactory;
use App\Factory\UserFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    /**
     * @var true
     */
    private bool $isEnvironmentTest = false;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }
    public function load(ObjectManager $manager): void
    {
        $users = UserFactory::createMany(20,function (){
            return['password' => $this->isEnvironmentTest ? 'password' : $this->passwordHasher->hashPassword(
                    new User(),
                    'password'
                )];
        });
        $projects = ProjectFactory::createMany(10, function () use ($users) {
            // Associer plusieurs utilisateurs à chaque projet
            $assignedUsers = [];
            for ($i = 0; $i < rand(1, 5); $i++) {
                $assignedUsers[] = $users[array_rand($users)];
            }
            return ['users' => $assignedUsers];
        });
        $statuses = StatusFactory::createMany(3);
        TaskFactory::createMany(80, function () use ($statuses, $projects) {
            $project = $projects[array_rand($projects)];
            $projectUsers = $project->getUsers()->toArray();
            $user = $project->getUsers()[array_rand($projectUsers)];
            return ['status' => $statuses[array_rand($statuses)], 'project' => $project, 'user' => $user];
        });
        $manager->flush();
    }
}