<?php

namespace App\Command;

use App\Entity\Main\SuperUser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:generate-super-admin',
    description: 'Add a short description for your command',
)]
class GenerateSuperAdminCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface      $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $entityManager = $this->entityManager;
        $userPasswordHasher = $this->userPasswordHasher;

        $adminUsername = "admin@mail.com";
        $adminPswd = "Admin@Navira24!";

        $userAdmin = $entityManager->getRepository(SuperUser::class)->findOneBy([
            'email' => $adminUsername
        ]);

        if (!$userAdmin) {
            $userAdmin = new SuperUser();
            $userAdmin->setEmail($adminUsername);
        }

        $userAdmin->setRoles(['ROLE_SUPERADMIN']);

        $userAdmin->setPassword($userPasswordHasher->hashPassword($userAdmin, $adminPswd));

        $entityManager->persist($userAdmin);
        $entityManager->flush();

        $io->success('Utilisateur admin générer avec succès: ' . $adminUsername);

        return Command::SUCCESS;
    }
}
