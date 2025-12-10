<?php 

namespace App\Controller\Dynamic;

use App\Entity\Dynamic\Equipe;
use App\Entity\Dynamic\Taches;
use App\Service\DatabaseSwitcher;
use App\Service\DynamicEntityManagerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/equipe')]
final class EquipeController extends AbstractController
{
    private DatabaseSwitcher $databaseSwitcher;
    private DynamicEntityManagerProvider $dynamicEntityManagerProvider;
    public function __construct(
        DatabaseSwitcher $databaseSwitcher,
        DynamicEntityManagerProvider $dynamicEntityManagerProvider,
        )
    {
        $this->databaseSwitcher = $databaseSwitcher;
        $this->dynamicEntityManagerProvider = $dynamicEntityManagerProvider;
    }

    #[Route('/', name: 'app_equipe_index', methods: ['POST', 'GET'])]
    public function index(
        Request     $request
    ): Response
    {

      
        $database_name = "facebook";
        $this->databaseSwitcher->switchDatabase($database_name);
        $entityManager = $this->dynamicEntityManagerProvider->getEntityManager();
      
        $equipes = $entityManager->getRepository(Equipe::class)->findAll();

        // Logique pour afficher la liste des tâches
        return $this->render('espace_client/equipe/index.html.twig', [
            'equipes' => $equipes,
        ]);
    }
}