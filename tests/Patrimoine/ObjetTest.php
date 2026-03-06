<?php

namespace App\Tests\Patrimoine;

use App\Entity\Categorie;
use App\Entity\Objet;
use PHPUnit\Framework\TestCase;

class ObjetTest extends TestCase
{
    private function createCategorie(string $nom = 'Poterie'): Categorie
    {
        $cat = new Categorie();
        $cat->setNomCategorie($nom);
        return $cat;
    }

    public function testSettersAndGetters(): void
    {
        $objet = new Objet();
        $objet->setNom('Amphore carthaginoise');
        $objet->setDescriptionHistorique('Vase en terre cuite utilisé pour le transport d\'huile.');
        $objet->setEpoque('IVe siècle av. J.-C.');
        $objet->setOrigine('Carthage, Tunisie');
        $objet->setMateriaux('Terre cuite');

        $this->assertSame('Amphore carthaginoise', $objet->getNom());
        $this->assertSame('IVe siècle av. J.-C.', $objet->getEpoque());
        $this->assertSame('Carthage, Tunisie', $objet->getOrigine());
        $this->assertSame('Terre cuite', $objet->getMateriaux());
    }

    public function testNewObjetHasNoId(): void
    {
        $objet = new Objet();

        $this->assertNull($objet->getIdObjet());
    }

    public function testNewObjetHasNoMedias(): void
    {
        $objet = new Objet();

        $this->assertCount(0, $objet->getMedias());
    }

    public function testOptionalFieldsCanBeNull(): void
    {
        $objet = new Objet();
        $objet->setNom('Statuette');
        $objet->setDescriptionHistorique(null);
        $objet->setEpoque(null);
        $objet->setOrigine(null);
        $objet->setMateriaux(null);

        $this->assertNull($objet->getDescriptionHistorique());
        $this->assertNull($objet->getEpoque());
        $this->assertNull($objet->getOrigine());
        $this->assertNull($objet->getMateriaux());
    }

    public function testObjetCanBeAssignedToCategorie(): void
    {
        $categorie = $this->createCategorie('Archéologie');
        $objet = new Objet();
        $objet->setNom('Mosaïque romaine');
        $objet->setCategorie($categorie);

        $this->assertSame($categorie, $objet->getCategorie());
        $this->assertSame('Archéologie', $objet->getCategorie()->getNomCategorie());
    }

    public function testObjetCategorieCanbeSwitched(): void
    {
        $cat1 = $this->createCategorie('Poterie');
        $cat2 = $this->createCategorie('Sculpture');

        $objet = new Objet();
        $objet->setNom('Figurine');
        $objet->setCategorie($cat1);
        $this->assertSame('Poterie', $objet->getCategorie()->getNomCategorie());

        $objet->setCategorie($cat2);
        $this->assertSame('Sculpture', $objet->getCategorie()->getNomCategorie());
    }

    public function testToStringReturnsNom(): void
    {
        $objet = new Objet();
        $objet->setNom('Lampe à huile');

        $this->assertSame('Lampe à huile', (string) $objet);
    }
}
