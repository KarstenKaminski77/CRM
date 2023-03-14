<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class AdminController extends AbstractController
{
    #[Route('/', name: 'app_user')]
    #[Route('/admin/user/create')]
    #[Route('/admin/users')]
    #[Route('/admin/user/update')]
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function index(): Response
    {
        if($this->getUser() == null){

            $this->addFlash('danger', 'Your session expired due to inactivity, please login.');

            return $this->redirectToRoute('admin_login');
        }

        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/error', name: 'frontend_error_500')]
    public function frontend500ErrorAction(Request $request): Response
    {
        return $this->render('bundles/TwigBundle/Exception/error500.html.twig', [
            'type' => 'frontend',
            'id' => 0,
        ]);
    }
}
