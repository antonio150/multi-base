<?php

namespace App\Service;

use App\Entity\Main\Site;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\ORMSetup;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Yaml\Yaml;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

class DatabaseSwitcher
{
    private EntityManagerInterface $entityManager;
    private Connection $connection;
    private ManagerRegistry $doctrine;
    private KernelInterface $kernel;
    

    public function __construct(
        EntityManagerInterface $entityManager, 
        ManagerRegistry $doctrine,
        private DynamicEntityManagerProvider $provider,
        KernelInterface $kernel
    ) {
        $this->entityManager = $entityManager;
        $this->connection = $entityManager->getConnection();
        $this->doctrine = $doctrine;
        $this->kernel = $kernel;
    }

  
    /**
     * Méthode à APPELER via ton API pour tout faire dynamiquement
     */
    public function createDatabase(
        string $databaseName,
        string $sitBddUser,
        string $sitBddMdp
    ): array|JsonResponse
    {

        $dbUser = $sitBddUser;
        $dbPassword = $sitBddMdp;
        

        // 2. CRÉATION BASE + USER + DROITS
        try {
            $this->createDatabaseAndUser($databaseName, $dbUser, $dbPassword);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Erreur création DB/User : ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }

        // 3. Création et mise à jour du schéma directement (sans modifier doctrine.yaml)
        try {
            $params = $this->connection->getParams();
            $host = $params['host'];
            $dbType = $_ENV['DB_TYPE'] ?? 'mysql'; // Lecture du type de base de données depuis .env

            // Définir le driver et les paramètres spécifiques au type de base de données
            $driver = 'pdo_mysql';
            $charset = 'utf8mb4';

            // Port par défaut selon le type de base de données
            $defaultPort = 3306;
            $port = $params['port'] ?? $defaultPort;

            $connectionParams = [
                'dbname'   => $databaseName,
                'user'     => $dbUser,
                'password' => $dbPassword,
                'host'     => $host,
                'port'     => $port,
                'driver'   => $driver,
                'charset'  => $charset,
            ];

            // Configuration pour les entités Dynamic
            $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
                [$this->kernel->getProjectDir() . '/src/Entity/Dynamic'],
                true
            );

            // Connexion et EntityManager
            $connection = DriverManager::getConnection($connectionParams, $ormConfig);
            $entityManager = new EntityManager($connection, $ormConfig);

            // Mise à jour du schéma
            $this->updateSchemaFromMetadata($entityManager);
        } catch (\Throwable $e) {
            return new JsonResponse([
                'message' => 'Erreur application du schéma : ' . $e->getMessage(),
                'status' => 500,
            ], 500);
        }

        return [
            'databaseName' => $databaseName,
        ];
    }

    // Les méthodes updateDoctrineConfig et runSchemaUpdate ont été supprimées car remplacées par updateSchemaFromMetadata

    /**
     * Crée BDD, USER et assigne les droits en utilisant le SGBD configuré
     */
    private function createDatabaseAndUser(string $databaseName, string $dbUser, string $dbPwd): void
    {
        $conn = $this->connection; // On suppose connecté avec le super-utilisateur/admin/root
        $dbType = $_ENV['DB_TYPE'] ?? 'mysql'; // Lecture du type de base de données depuis .env
            // MySQL (comportement par défaut)
            try {
                // Drop if exists to ensure clean state
                $conn->executeStatement("DROP DATABASE IF EXISTS `$databaseName`");
                // Crée la base de données
                $conn->executeStatement("CREATE DATABASE `$databaseName` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                // Crée l'utilisateur si pas encore créé
                $conn->executeStatement(
                    "CREATE USER IF NOT EXISTS '{$dbUser}'@'%' IDENTIFIED BY :pwd",
                    ['pwd' => $dbPwd]
                );
                
                // Donne tous les droits sur la base
                $conn->executeStatement("GRANT ALL PRIVILEGES ON `{$databaseName}`.* TO '{$dbUser}'@'%'");
                
                // Applique les droits
                $conn->executeStatement("FLUSH PRIVILEGES");
            } catch (\Exception $e) {
                // Gère les erreurs spécifiques à MySQL
                throw $e;
            }
        
    }

    public function dropDatabase(string $databaseName): void
    {
        $conn = $this->connection; // On suppose connecté avec le super-utilisateur/admin/root
        $dbType = $_ENV['DB_TYPE'] ?? 'mysql'; // Lecture du type de base de données depuis .env

        // MySQL
        $conn->executeStatement("DROP DATABASE IF EXISTS `$databaseName`");
        
    }


    public function switchDatabase(string $databaseName): void
    {
        // 🔥 Nouvelle URL de connexion

        // Récupérer l'URL de la base de données actuelle
        $databaseUrl = $_ENV['DATABASE_URL_MAIN'] ?? getenv('DATABASE_URL_MAIN');
        $schemaManager = $this->connection->createSchemaManager();
        $existingDatabases = $schemaManager->listDatabases();
        $params = $this->connection->getParams();
        $dbType = $_ENV['DB_TYPE'] ?? 'mysql'; // Lecture du type de base de données depuis .env

        // Récupérer l'EntityManager pour la base principale
        $mainEntityManager = $this->doctrine->getManager('default');

        $site = $mainEntityManager
        ->getRepository(\App\Entity\Main\Site::class)
        ->findOneBy(['sitBddNom' => $databaseName]);
        
        $siteBddUser = $site->getSitBddUser();
        $siteBddMdp = $site->getSitBddMdp();

        // Définir le driver et les paramètres spécifiques au type de base de données
        $driver = 'pdo_mysql';
        $charset = 'utf8mb4';
        $serverVersion = '10.4.32-MariaDB';
        
        // Port par défaut selon le type de base de données
        $defaultPort = 3306;
        $port = $params['port'] ?? $defaultPort;

        try {
            $testConnection = DriverManager::getConnection([
                'driver' => $driver,
                'host' => $params['host'],
                'port' => $port,
                'dbname' => $databaseName,
                'user' => $siteBddUser,
                'password' => $siteBddMdp,
                'charset' => $charset,
            ]);
         
            // Exécuter une requête simple pour vérifier l'accès
            $testConnection->executeQuery('SELECT 1');
            $databaseExistsWithUser = true;
            $errors[] = "base de données avec cet utilisateur";
            $testConnection->close();
           
            $newUrl = "mysql://".rawurlencode($siteBddUser).":".rawurlencode($siteBddMdp)."@".$params['host'].":".$port."/".$databaseName."?serverVersion=".$serverVersion."&charset=".$charset;
            
        } catch (\Exception $e) {
 
            $newUrl = $_ENV['DATABASE_URL_DYNAMIC_MYSQL'] ?? getenv('DATABASE_URL_DYNAMIC_MYSQL');
           
            // Fallback sur la variable générique si les spécifiques ne sont pas définies
            if (empty($newUrl)) {
                $newUrl = $_ENV['DATABASE_URL_DYNAMIC'] ?? getenv('DATABASE_URL_DYNAMIC');
            }
        }

        // ⚡ Modifier dynamiquement la connexion de `dynamic`
        $params = $this->entityManager->getConnection()->getParams();
        $params['url'] = $newUrl;

        // 🏗️ Créer un nouveau `EntityManager` pour la base dynamique
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../Entity/Dynamic'], // 📌 Chemin des entités
            true
        );
        $newConnection = DriverManager::getConnection( $testConnection->getParams());
        $newEntityManager = new EntityManager($newConnection, $config);

       
        
        // 🔄 Mettre à jour l'EntityManager courant
        $this->entityManager = $newEntityManager;
        // 🧠 Stocke dans le provider
        $this->provider->setEntityManager($newEntityManager);
    }

    /**
     * Met à jour le schéma de base de données en utilisant SchemaTool
     * comme dans UpdateTenantSchemasCommand
     */
    private function updateSchemaFromMetadata(EntityManager $entityManager): void
    {
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();
        if (count($metadata) > 0) {
            $schemaTool = new \Doctrine\ORM\Tools\SchemaTool($entityManager);
            $schemaTool->updateSchema($metadata, true);
        }
    }
}
