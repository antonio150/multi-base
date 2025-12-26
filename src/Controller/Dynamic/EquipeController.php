<?php 

namespace App\Controller\Dynamic;

use App\Entity\Dynamic\Equipe;
use App\Entity\Dynamic\Taches;
use App\Service\DatabaseSwitcher;
use App\Service\DynamicEntityManagerProvider;
use App\Service\RoleCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    private DatabaseSwitcher $databaseSwitcher;
    private DynamicEntityManagerProvider $dynamicEntityManagerProvider;
    private RoleCheckerService $roleCheckerService;
    public function __construct(
        DatabaseSwitcher $databaseSwitcher,
        RoleCheckerService $roleCheckerService,
        DynamicEntityManagerProvider $dynamicEntityManagerProvider,
        )
    {
        $this->databaseSwitcher = $databaseSwitcher;
        $this->dynamicEntityManagerProvider = $dynamicEntityManagerProvider;
        $this->roleCheckerService = $roleCheckerService;
    }

    #[Route('/', name: 'app_equipe_index', methods: ['POST', 'GET'])]
    public function index(
        Request     $request
    ): Response
    {

        // Vérifier les rôles via le service
        $requiredRoles = ['ROLE_SUPERVISEUR','ROLE_AGENT', 'ROLE_ADMIN'];
        $roleCheck = $this->roleCheckerService->checkUserRolesWithResponse($request, $requiredRoles);
        // Si le résultat est une JsonResponse, retourner l'erreur
        if ($roleCheck instanceof JsonResponse) {
            return $roleCheck;
        }
        $database_name = $roleCheck['database_name'];
        $this->databaseSwitcher->switchDatabase($database_name);
        $entityManager = $this->dynamicEntityManagerProvider->getEntityManager();
      
        $equipes = $entityManager->getRepository(Equipe::class)->findAll();

        // Logique pour afficher la liste des tâches
        return $this->render('espace_client/equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }
}