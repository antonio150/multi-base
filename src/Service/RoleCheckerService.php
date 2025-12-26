<?php

namespace App\Service;

use App\Entity\Main\Site;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class RoleCheckerService
{
    private TokenRoleChecker $tokenRoleChecker;
    private EntityManagerInterface $mainEntityManager;

    public function __construct(
        TokenRoleChecker $tokenRoleChecker,
        EntityManagerInterface $mainEntityManager
    ) {
        $this->tokenRoleChecker = $tokenRoleChecker;
        $this->mainEntityManager = $mainEntityManager;
    }

    /**
     * Vérifie les rôles de l'utilisateur et retourne les informations nécessaires.
     *
     * @param Request $request La requête HTTP
     * @param array $requiredRoles Les rôles requis
     * @return array Résultat contenant status, idUtilisateur, id_site et database_name
     * @throws \Exception Si les rôles ne sont pas valides ou si le site est introuvable
     */
    public function checkUserRoles(Request $request, array $requiredRoles): array
    {
        $roleCheckResult = $this->tokenRoleChecker->checkRoles($request, $requiredRoles);

        if ($roleCheckResult['status'] !== 200) {
            throw new \Exception($roleCheckResult['message'], $roleCheckResult['status']);
        }

        if (!isset($roleCheckResult['id_site']) || !isset($roleCheckResult['idUtilisateur'])) {
            throw new \Exception('Informations utilisateur ou site manquantes.', 400);
        }

        // Recherche du site dans la base principale
        $site = $this->mainEntityManager
            ->getRepository(Site::class)
            ->find($roleCheckResult['id_site']);

        if (!$site) {
            throw new \Exception('Site introuvable pour l\'ID : ' . $roleCheckResult['id_site'], 404);
        }

        $database_name = $site->getSitBddNom();
        if (!$database_name) {
            throw new \Exception('Nom de la base de données non défini pour le site.', 500);
        }

        return [
            'status' => $roleCheckResult['status'],
            'idUtilisateur' => $roleCheckResult['idUtilisateur'],
            'id_site' => $roleCheckResult['id_site'],
            'database_name' => $database_name,
        ];
    }

    /**
     * Vérifie les rôles et retourne une réponse JSON en cas d'erreur.
     *
     * @param Request $request La requête HTTP
     * @param array $requiredRoles Les rôles requis
     * @return array|JsonResponse Retourne les infos utilisateur ou une réponse JSON en cas d'erreur
     */
    public function checkUserRolesWithResponse(Request $request, array $requiredRoles)
    {
        try {
            return $this->checkUserRoles($request, $requiredRoles);
        } catch (\Exception $e) {
            return new JsonResponse([
                'status' => $e->getCode() ?: 500,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}