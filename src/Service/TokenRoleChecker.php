<?php

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;

class TokenRoleChecker
{
    private $jwtManager;

    public function __construct(JWTTokenManagerInterface $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function checkRoles(Request $request, array $requiredRoles): array
    {
        // Récupérer le token depuis l'en-tête Authorization
        $authHeader = $request->getSession()->get('tokken');
        if (!$authHeader) {
            return [
                'status' => 400,
                'message' => 'Token JWT manquant ou invalide.',
            ];
        }

        $token = $authHeader;

        // Décoder le token pour obtenir le payload
        try {
            $payload = $this->jwtManager->parse($token);
        } catch (\Exception $e) {
            return [
                'status' => 400,
                'message' => 'Token JWT invalide ou corrompu : ' . $e->getMessage(),
            ];
        }

        // Vérifier la présence des rôles dans le payload
        if (!isset($payload['roles']) || !is_array($payload['roles'])) {
            return [
                'status' => 400,
                'message' => 'Aucun rôle trouvé dans le token.',
            ];
        }
        $userRoles = $payload['roles'];
        // Vérification des rôles requis pour les autres utilisateurs
        $hasRequiredRole = !empty(array_intersect($userRoles, $requiredRoles));
        if (!$hasRequiredRole) {
            return [
                'status' => 400,
                'message' => 'Vous n\'avez pas les permissions nécessaires pour accéder à cette ressource.',
            ];
        }

        // Succès pour les utilisateurs non-super-admin
        return [
            'status' => 200,
            'message' => 'Accès autorisé.',
            'roles' => $userRoles,
            'idUtilisateur' => $payload['idUtilisateur'] ?? null,
            'id_site' => $payload['id_site'] ?? null,
        ];
    }
}