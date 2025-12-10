<?php

namespace App\Entity\Dynamic;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Controller\Dynamic\Equipe\CreateEquipeController;
use App\Controller\Dynamic\Equipe\DeleteEquipeController;
use App\Controller\Dynamic\Equipe\ListeEquipeController;
use App\Controller\Dynamic\Equipe\ListPersonnesInEquipeController;
use App\Controller\Dynamic\Equipe\ListPersonnesNotInEquipeController;
use App\Controller\Dynamic\Equipe\OneEquipeController;
use App\Controller\Dynamic\Equipe\UpdateEquipeController;
use App\Controller\Dynamic\TacheTravailleur\listeTravailleurDansEquipeController;
use App\Controller\Dynamic\TacheTravailleur\listeTravailleurSeulementDansEquipeController;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Annotation\Groups;
use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Uid\Uuid;

/**
 * Equipe
 */
#[ORM\Table(name: 'equipe')]
#[ORM\Index(name: 'WDIDX_Equipe_EQP_Nom', columns: ['EQP_Nom'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Equipe
{
  
    #[ORM\Id]
    #[ORM\Column(name: 'ID_Equipe', type: 'string', length: 36, unique: true, nullable: false)]
    private ?string $id = null;

    /**
     * @var string
     */
    #[ORM\Column(name: 'EQP_Nom', type: 'string', length: 50, nullable: false)]
    private $eqpNom;

   
    public function __construct()
    {
        $this->id = Uuid::v4()->toRfc4122();
    }

    // Getter for idEquipe
    public function getId(): ?string
    {
        return $this->id;
    }


    // Getter for eqpNom
    public function getEqpNom(): ?string
    {
        return $this->eqpNom;
    }

    // Setter for eqpNom
    public function setEqpNom(string $eqpNom): self
    {
        $this->eqpNom = $eqpNom;

        return $this;
    }

    // Method to represent the object as a string
    public function __toString(): string
    {
        return $this->getEqpNom();
    }

    
}
