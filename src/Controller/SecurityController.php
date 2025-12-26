<?php

namespace App\Controller;

use App\Entity\Dynamic\Utilisateur;
use App\Entity\Main\SuperUser;
use App\Service\RoleCheckerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    private RoleCheckerService $roleCheckerService;

    public function __construct(RoleCheckerService $roleCheckerService)
    {
        $this->roleCheckerService = $roleCheckerService;
    }
    #[Route(path: '/login', name: 'app_login_main')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        // Si l'utilisateur est déjà connecté, rediriger vers le tableau de bord
        /** @var SuperUser $currentUser */
        $currentUser = $this->getUser();
        if ($currentUser) {
            return $this->redirectToRoute('app_home');
        }

        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/login-dynamic', name: 'app_login_dynamic')]
    public function login_dynamic(Request $request): Response
    {
        if ($request->getSession()->has('tokken')) {
            return $this->redirectToRoute('app_equipe_index');
        }

        return $this->render('security/login.html.twig',[
            'error' => null,
            'last_username' => null,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout_main')]
    public function logout(SessionInterface $session): void
    {
       
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/logout-dynamic', name: 'app_logout_dynamic')]
    public function logout_dynamic(Request $request): Response
    {
        $request->getSession()->remove('tokken');
        
        return $this->redirectToRoute('app_login_dynamic');
  }

    #[Route('/access-denied', name: 'app_access_denied')]
    public function accessDenied(): Response
    {
        return $this->render('errors/error403.html.twig', [],
            new Response('', Response::HTTP_FORBIDDEN)
        );
    }
    
}
