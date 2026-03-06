<?php

namespace App\Entity;

use App\Repository\CategorieRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategorieRepository::class)]
class Categorie
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $idCategorie = null;

    #[ORM\Column(length: 255)]
    private ?string $nomCategorie = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $descriptionType = null;

    /**
     * @var Collection<int, Objet>
     */
    #[ORM\OneToMany(mappedBy: 'categorie', targetEntity: Objet::class, orphanRemoval: true)]
    private Collection $objets;

    public function __construct()
    {
        $this->objets = new ArrayCollection();
    }

    public function getIdCategorie(): ?int
    {
        return $this->idCategorie;
    }

    public function getNomCategorie(): ?string
    {
        return $this->nomCategorie;
    }

    public function setNomCategorie(string $nomCategorie): static
    {
        $this->nomCategorie = $nomCategorie;

        return $this;
    }

    public function getDescriptionType(): ?string
    {
        return $this->descriptionType;
    }

    public function setDescriptionType(?string $descriptionType): static
    {
        $this->descriptionType = $descriptionType;

        return $this;
    }

    /**
     * @return Collection<int, Objet>
     */
    public function getObjets(): Collection
    {
        return $this->objets;
    }

    public function addObjet(Objet $objet): static
    {
        if (!$this->objets->contains($objet)) {
            $this->objets->add($objet);
            $objet->setCategorie($this);
        }

        return $this;
    }

    public function removeObjet(Objet $objet): static
    {
        if ($this->objets->removeElement($objet)) {
            if ($objet->getCategorie() === $this) {
                $objet->setCategorie(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return (string) $this->nomCategorie ?: 'Categorie #'.$this->idCategorie;
    }
}
