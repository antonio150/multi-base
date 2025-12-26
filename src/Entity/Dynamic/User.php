<?php 

namespace App\Entity\Dynamic;

use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity()]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, name: 'ID_User', length: 36)]
    private ?string $id = null;

    #[ORM\Column(name: 'USER_Login', type: 'string', length: 50, nullable: true)]
    private ?string $userLogin = null;

   
    #[ORM\Column(name: 'USER_MotDePasse', type: 'string', length: 255, nullable: true)]
    private ?string $userMotDePasse = null;

    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUserLogin(): ?string
    {
        return $this->userLogin;
    }

    public function setUserLogin(string $userLogin): static
    {
        $this->userLogin = $userLogin;
        return $this;
    }

    public function getUserIdentifier(): string
    {
        return (string) $this->userLogin;
    }

    public function getRoles(): array
    {
        return ['ROLE_AGENT'];
    }

   
    public function setUserMotDePasse(string $UserMotDePasse): static
    {
        $this->userMotDePasse = $UserMotDePasse;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->userMotDePasse;
    }

    public function eraseCredentials(): void
    {
    }
}