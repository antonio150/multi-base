<?php

namespace App\Entity\Main;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Main\FileStockMain;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use App\Entity\Traits\TimestampableTrait;
use Symfony\Component\Uid\Uuid;

/**
 * Site
 */
#[ORM\Table(name: 'site')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class Site
{
 
    #[ORM\Id]
    #[ORM\Column(name: 'ID_Site', unique: true, type: 'string', length: 36, nullable: false)]
    private ?string $id = null;

    #[ORM\Column(name: 'SIT_RaisonSociale', type: 'string', length: 255, nullable: false)]
    private string $sitRaisonsociale;

    #[ORM\Column(name: 'SIT_Adresse', type: 'string', length: 255, nullable: false)]
    private string $sitAdresse;

    #[ORM\Column(name: 'SIT_Tel', type: 'string', length: 30, nullable: false)]
    private string $sitTel;

    #[ORM\Column(name: 'SIT_Mail', type: 'string', length: 255, nullable: false)]
    private string $sitMail;

    #[ORM\Column(name: 'SIT_Code', type: 'string', length: 50, nullable: false)]
    private string $sitCode;

    #[Assert\File(mimeTypes: ['image/jpeg', 'image/png', 'image/gif'])]
    private ?File $logoFile = null;

    #[ORM\Column(name: 'SIT_BDD_Nom', type: 'string', length: 100, nullable: false)]
    private string $sitBddNom;

    #[ORM\Column(name: 'SIT_BDD_User', type: 'string', length: 100, nullable: false)]
    private string $sitBddUser;

    #[ORM\Column(name: 'SIT_BDD_Mdp', type: 'string', length: 100, nullable: false)]
    private string $sitBddMdp;

    #[ORM\Column(name: 'SIT_Actif', type: 'boolean', nullable: false)]
    private bool $estActif = true;


    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getSitRaisonsociale(): ?string
    {
        return $this->sitRaisonsociale;
    }

    public function setSitRaisonsociale(string $sitRaisonsociale): static
    {
        $this->sitRaisonsociale = $sitRaisonsociale;
        return $this;
    }

    public function getSitAdresse(): ?string
    {
        return $this->sitAdresse;
    }

    public function setSitAdresse(string $sitAdresse): static
    {
        $this->sitAdresse = $sitAdresse;
        return $this;
    }

    public function getSitTel(): ?string
    {
        return $this->sitTel;
    }

    public function setSitTel(string $sitTel): static
    {
        $this->sitTel = $sitTel;
        return $this;
    }

    public function getSitMail(): ?string
    {
        return $this->sitMail;
    }

    public function setSitMail(string $sitMail): static
    {
        $this->sitMail = $sitMail;
        return $this;
    }

    public function getSitCode(): ?string
    {
        return $this->sitCode;
    }

    public function setSitCode(string $sitCode): static
    {
        $this->sitCode = $sitCode;
        return $this;
    }

    public function getSitBddNom(): ?string
    {
        return $this->sitBddNom;
    }

    public function setSitBddNom(string $sitBddNom): static
    {
        $this->sitBddNom = $sitBddNom;
        return $this;
    }

    public function getSitBddUser(): ?string
    {
        return $this->sitBddUser;
    }

    public function setSitBddUser(string $sitBddUser): static
    {
        $this->sitBddUser = $sitBddUser;
        return $this;
    }

    public function getSitBddMdp(): ?string
    {
        return $this->sitBddMdp;
    }

    public function setSitBddMdp(string $sitBddMdp): static
    {
        $this->sitBddMdp = $sitBddMdp;
        return $this;
    }

    public function isestActif(): bool
    {
        return $this->estActif;
    }

    public function setestActif(bool $estActif): static
    {
        $this->estActif = $estActif;
        return $this;
    }

   
   
}