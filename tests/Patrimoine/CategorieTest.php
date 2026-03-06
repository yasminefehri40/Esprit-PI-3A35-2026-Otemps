<?php

namespace App\Tests\Patrimoine;

use App\Entity\Categorie;
use App\Entity\Objet;
use PHPUnit\Framework\TestCase;

class CategorieTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Poterie');
        $categorie->setDescriptionType('Ensemble des objets en terre cuite de Tunisie.');

        $this->assertSame('Poterie', $categorie->getNomCategorie());
        $this->assertSame('Ensemble des objets en terre cuite de Tunisie.', $categorie->getDescriptionType());
    }

    public function testNewCategorieHasNoId(): void
    {
        $categorie = new Categorie();

        $this->assertNull($categorie->getIdCategorie());
    }

    public function testNewCategorieHasNoObjets(): void
    {
        $categorie = new Categorie();

        $this->assertCount(0, $categorie->getObjets());
    }

    public function testDescriptionTypeIsOptional(): void
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Textile');
        $categorie->setDescriptionType(null);

        $this->assertNull($categorie->getDescriptionType());
    }

    public function testAddObjet(): void
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Bijouterie');

        $objet = new Objet();
        $objet->setNom('Collier en or');

        $categorie->addObjet($objet);

        $this->assertCount(1, $categorie->getObjets());
        $this->assertSame($categorie, $objet->getCategorie());
    }

    public function testAddObjetDoesNotDuplicate(): void
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Céramique');

        $objet = new Objet();
        $objet->setNom('Plat décoratif');

        $categorie->addObjet($objet);
        $categorie->addObjet($objet); // deuxième ajout identique

        $this->assertCount(1, $categorie->getObjets());
    }

    public function testRemoveObjet(): void
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Orfèvrerie');

        $objet = new Objet();
        $objet->setNom('Bague ancienne');

        $categorie->addObjet($objet);
        $this->assertCount(1, $categorie->getObjets());

        $categorie->removeObjet($objet);
        $this->assertCount(0, $categorie->getObjets());
    }

    public function testToStringReturnsNomCategorie(): void
    {
        $categorie = new Categorie();
        $categorie->setNomCategorie('Sculpture');

        $this->assertSame('Sculpture', (string) $categorie);
    }
}
