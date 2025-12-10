<?php

namespace App\Command;

use App\Entity\Main\Site;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Doctrine\ORM\EntityManagerInterface;

#[AsCommand(name: 'app:update-multi-schema', description: 'Met à jour le schéma de toutes les bases de données dynamiques')]
class UpdateTenantSchemasCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private KernelInterface $kernel;

    public function __construct(
        EntityManagerInterface $entityManager,
        KernelInterface $kernel,
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }
    // commande pour la mise a jour multiple des bases de donnees selon le schema dans dynamic : ok
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $sites = $this->entityManager->getRepository(Site::class)->findAll();

        $defaultConnection = $this->entityManager->getConnection();
        $params           = $defaultConnection->getParams();

        $host = $params['host'];
        $driver = $params['driver'];
        $port = $params['port'] ?? ($driver === 'pdo_pgsql' ? 5432 : 3306);
        $charset = $driver === 'pdo_pgsql' ? 'utf8' : 'utf8mb4';

        foreach ($sites as $site) {
            /** @var Site $site */
            $dbName = $site->getSitBddNom();
            $dbUser = $site->getSitBddUser();
            $dbPwd  = $site->getSitBddMdp();

            $io->section("Mise à jour de la base : $dbName");

            $connectionParams = [
                'dbname'   => $dbName,
                'user'     => $dbUser,
                'password' => $dbPwd,
                'host'     => $host,
                'port'     => $port,
                'driver'   => $driver,
                'charset'  => $charset,
            ];

            try {
                $ormConfig = ORMSetup::createAttributeMetadataConfiguration(
                    [$this->kernel->getProjectDir() . '/src/Entity/Dynamic'],
                    true
                );

                $connection = DriverManager::getConnection($connectionParams, $ormConfig);
                $tenantEntityManager = new EntityManager($connection, $ormConfig);

                $metadata = $tenantEntityManager->getMetadataFactory()->getAllMetadata();
                if (count($metadata) > 0) {
                    $schemaTool = new SchemaTool($tenantEntityManager);
                    $schemaTool->updateSchema($metadata, true);
                }

                $io->success("[$dbName] Schéma mis à jour !");
            } catch (\Throwable $e) {
                $io->error("Erreur pour $dbName : " . $e->getMessage());
            }
        }

        $io->success('✅ Mise à jour des schémas pour toutes les bases terminée !');

        return Command::SUCCESS;
    }
}
