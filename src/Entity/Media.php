<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id')]
    private ?int $idMedia = null;

    #[ORM\Column(length: 50)]
    private ?string $typeMedia = null;

    #[ORM\Column(length: 512)]
    private ?string $lienFichier = null;

    #[ORM\ManyToOne(inversedBy: 'medias')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Objet $objet = null;

    public function getIdMedia(): ?int
    {
        return $this->idMedia;
    }

    public function getTypeMedia(): ?string
    {
        return $this->typeMedia;
    }

    public function setTypeMedia(string $typeMedia): static
    {
        $this->typeMedia = $typeMedia;

        return $this;
    }

    public function getLienFichier(): ?string
    {
        return $this->lienFichier;
    }

    public function setLienFichier(string $lienFichier): static
    {
        $this->lienFichier = $lienFichier;

        return $this;
    }

    public function getObjet(): ?Objet
    {
        return $this->objet;
    }

    public function setObjet(?Objet $objet): static
    {
        $this->objet = $objet;

        return $this;
    }

    public function __toString(): string
    {
        return sprintf('%s (%s)', $this->typeMedia ?? 'Media', $this->lienFichier ?? '');
    }
}
