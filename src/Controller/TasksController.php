<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TasksController extends AbstractController
{
    private $em;
    function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/tasks', name: 'tasks_dashboard')]
    public function index(): Response
    {
        if($this->getUser() == null){

            $this->addFlash('danger', 'Your session expired due to inactivity, please login.');

            return $this->redirectToRoute('tasks_login');
        }

        return $this->render('tasks/index.html.twig');
    }
}
