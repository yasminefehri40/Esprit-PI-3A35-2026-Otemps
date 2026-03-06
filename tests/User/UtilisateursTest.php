<?php

namespace App\Tests\User;

use App\Entity\Utilisateurs;
use PHPUnit\Framework\TestCase;

class UtilisateursTest extends TestCase
{
    public function testSettersAndGetters(): void
    {
        $user = new Utilisateurs();
        $user->setNom('Dupont');
        $user->setPrenom('Jean');
        $user->setEmail('Jean.Dupont@Example.COM');
        $user->setMotdepasse('hashed_password_123');
        $user->setDateinscription(new \DateTime('2024-01-15'));
        $user->setRole('ROLE_USER');

        $this->assertSame('Dupont', $user->getNom());
        $this->assertSame('Jean', $user->getPrenom());
        // setEmail normalise en minuscules
        $this->assertSame('jean.dupont@example.com', $user->getEmail());
        $this->assertSame('hashed_password_123', $user->getMotdepasse());
        $this->assertSame('ROLE_USER', $user->getRole());
    }

    public function testEmailIsNormalizedToLowercase(): void
    {
        $user = new Utilisateurs();
        $user->setEmail('ADMIN@OTEMPS.FR');

        $this->assertSame('admin@otemps.fr', $user->getEmail());
        $this->assertSame('admin@otemps.fr', $user->getUserIdentifier());
    }

    public function testGetRolesReturnsArray(): void
    {
        $user = new Utilisateurs();
        $user->setRole('ROLE_ADMIN');

        $this->assertIsArray($user->getRoles());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testGetPasswordReturnsMdp(): void
    {
        $user = new Utilisateurs();
        $user->setMotdepasse('$2y$13$hashed');

        $this->assertSame('$2y$13$hashed', $user->getPassword());
    }

    public function testFaceImageIsNullByDefault(): void
    {
        $user = new Utilisateurs();

        $this->assertNull($user->getFaceImage());
    }

    public function testSetFaceImage(): void
    {
        $user = new Utilisateurs();
        $user->setFaceImage('data:image/png;base64,ABCD==');

        $this->assertSame('data:image/png;base64,ABCD==', $user->getFaceImage());
    }

    public function testEraseCredentialsDoesNothing(): void
    {
        $user = new Utilisateurs();
        $user->setMotdepasse('secret');
        $user->eraseCredentials();

        // Le mot de passe ne doit pas être effacé (c'est le hash en base)
        $this->assertSame('secret', $user->getMotdepasse());
    }

    public function testDefaultRoleIsUserIfNotSet(): void
    {
        $user = new Utilisateurs();
        // La propriété a une valeur par défaut 'ROLE_USER' dans l'entité
        $this->assertSame('ROLE_USER', $user->getRole());
    }
}
