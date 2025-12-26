<?php
namespace App\Security;

use App\Entity\Dynamic\User;
use App\Entity\Dynamic\Utilisateur;
use App\Service\DatabaseSwitcher;
use App\Service\DynamicEntityManagerProvider;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Serializer\SerializerInterface;

class UserDynamicAuthenticator extends AbstractAuthenticator
{

    public function __construct(
        private DatabaseSwitcher $databaseSwitcher,
        private EntityManagerInterface $mainEntityManager,
        private JWTTokenManagerInterface $jwtManager,
        private RouterInterface $router,
        private DynamicEntityManagerProvider $dynamicEntityManagerProvider,
    ) {
        $this->dynamicEntityManagerProvider = $dynamicEntityManagerProvider;
    }

   
    public function supports(Request $request): bool
    {
        $matches = $request->getPathInfo() === '/login-dynamic' && $request->isMethod('POST');
        error_log('[UserDynamicAuthenticator] supports=' . ($matches ? 'true' : 'false') . ' path=' . $request->getPathInfo());
        return $matches;
    }

    public function authenticate(Request $request): Passport
    {
        error_log('[UserDynamicAuthenticator] authenticate called. IP=' . $request->getClientIp());
       
       
        $data = null;
        $contentType = $request->headers->get('Content-Type') ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        }

        if (!is_array($data) || empty($data)) {
            // fallback pour form-data / x-www-form-urlencoded
            $data = $request->request->all();
        }

        $username = $data['_username'] ?? $request->request->get('_username');
        $password = $data['_password'] ?? $request->request->get('_password');
        $codeVoucher = $data['sitCode'] ?? $request->request->get('sitCode');

        error_log(sprintf('[UserDynamicAuthenticator] credentials: username=%s sitCode=%s', $username ?? 'NULL', $codeVoucher ?? 'NULL'));

        if (!$username || !$password) {
            throw new AuthenticationException('Les paramètres d\'authentification sont manquants.');
        }

        $site = $this->mainEntityManager
            ->getRepository(\App\Entity\Main\Site::class)
            ->findOneBy(['sitCode' => $codeVoucher]);
        $databasename = $site->getSitBddNom();
         $this->databaseSwitcher->switchDatabase($databasename);
        $entityManager = $this->dynamicEntityManagerProvider->getEntityManager();

        $entityManager->getRepository(User::class)->findOneBy(['userLogin' => $username]);

        return new Passport(
            new UserBadge($username, function ($userIdentifier) use ($entityManager) {
                return $entityManager->getRepository(User::class)->findOneBy(['userLogin' => $userIdentifier]);
            }),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    { 
        $user = $token->getUser();

        $data = null;
        $contentType = $request->headers->get('Content-Type') ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $data = json_decode($request->getContent(), true);
        }

        if (!is_array($data) || empty($data)) {
            // fallback pour form-data / x-www-form-urlencoded
            $data = $request->request->all();
        }
        $codeVoucher = $data['sitCode'] ?? $request->request->get('sitCode');
        $site = $this->mainEntityManager
            ->getRepository(\App\Entity\Main\Site::class)
            ->findOneBy(['sitCode' => $codeVoucher]);

        $id_site = $site->getId();
        $payload = [
            'username' => $user->getUserIdentifier(),
            'roles' => $user->getRoles(),
            'id_site' => $id_site,
            'idUtilisateur' => $user->getId(),
        ];
        $jwt = $this->jwtManager->createFromPayload($user, $payload);
        $request->getSession()->set('tokken', $jwt);
        // Redirect to espaceclient root to ensure session is used on next request
        return new \Symfony\Component\HttpFoundation\RedirectResponse(
            $this->router->generate('app_equipe_index')
        );


    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        error_log('[UserDynamicAuthenticator] authentication failure: ' . $exception->getMessage());
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new Response(json_encode($data), Response::HTTP_UNAUTHORIZED, ['Content-Type' => 'application/json']);
    }
}