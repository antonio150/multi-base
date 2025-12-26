<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Doctrine\ORM\EntityManagerInterface;
use Faker;
use App\Entity\Dynamic\Utilisateur;
use App\Entity\Dynamic\Personne;
use App\Entity\Dynamic\Profil;
use App\Entity\Dynamic\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Uid\Uuid;
use App\Service\DatabaseSwitcher;
use App\Service\DynamicEntityManagerProvider;

#[AsCommand(
    name: 'app:create-random-users',
    description: 'Crée des utilisateurs aléatoires dans la base de données spécifiée'
)]
class CreateRandomUsersCommand extends Command
{
    private $passwordHasher;
    private $databaseSwitcher;
    private $dynamicEntityManagerProvider;

    public function __construct(UserPasswordHasherInterface $passwordHasher, DatabaseSwitcher $databaseSwitcher, DynamicEntityManagerProvider $dynamicEntityManagerProvider)
    {
        parent::__construct();
        $this->passwordHasher = $passwordHasher;
        $this->databaseSwitcher = $databaseSwitcher;
        $this->dynamicEntityManagerProvider = $dynamicEntityManagerProvider;
        }

    protected function configure(): void
    {
        $this
            ->addArgument('database', InputArgument::REQUIRED, 'Nom de la base de données')
            ->addArgument('count', InputArgument::OPTIONAL, 'Nombre d\'utilisateurs à créer', 10);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $database = $input->getArgument('database');
        $count = $input->getArgument('count');

        $this->databaseSwitcher->switchDatabase($database);

        $faker = Faker\Factory::create('fr_FR');
        $output->writeln(sprintf('Création de %d utilisateurs dans la base de données %s...', $count, $database));

        for ($i = 0; $i < $count; $i++) {
           
            // Créer un utilisateur
            $user = new User();
            $user->setUserLogin($faker->userName);
           
            // Générer un mot de passe aléatoire
            $plainPassword = 'password123';
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setUserMotDePasse($hashedPassword);
           $this->dynamicEntityManagerProvider->getEntityManager()->persist($user);

            $output->writeln(sprintf('Utilisateur créé : %s', $user->getUserLogin()));
        }

        $this->dynamicEntityManagerProvider->getEntityManager()->flush();
        
        $output->writeln('<info>Les utilisateurs ont été créés avec succès!</info>');
        return Command::SUCCESS;
    }
}
