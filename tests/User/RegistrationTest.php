<?php

namespace App\Tests\User;

use App\Entity\Utilisateurs;
use PHPUnit\Framework\TestCase;

/**
 * Tests liés à la logique d'inscription (Registration).
 * On vérifie que les règles métier sur l'entité Utilisateurs
 * sont correctement appliquées lors de la création d'un compte.
 */
class RegistrationTest extends TestCase
{
    public function testNewUserHasNoId(): void
    {
        $user = new Utilisateurs();

        // Un utilisateur non persisté n'a pas encore d'ID
        $this->assertNull($user->getId());
    }

    public function testNewUserHasEmptyParticipations(): void
    {
        $user = new Utilisateurs();

        $this->assertCount(0, $user->getParticipations());
    }

    public function testUserCanSetAllRegistrationFields(): void
    {
        $user = new Utilisateurs();
        $date = new \DateTime('now');

        $user->setNom('Martin')
             ->setPrenom('Alice')
             ->setEmail('alice@example.com')
             ->setMotdepasse('$2y$13$hashedpassword')
             ->setDateinscription($date);

        $this->assertSame('Martin', $user->getNom());
        $this->assertSame('Alice', $user->getPrenom());
        $this->assertSame('alice@example.com', $user->getEmail());
        $this->assertSame('$2y$13$hashedpassword', $user->getMotdepasse());
        $this->assertSame($date, $user->getDateinscription());
    }

    public function testDefaultRoleAfterRegistrationIsUser(): void
    {
        $user = new Utilisateurs();
        // Simule ce que fait le contrôleur à l'inscription
        $user->setRole('ROLE_USER');

        $this->assertSame('ROLE_USER', $user->getRole());
        $this->assertNotSame('ROLE_ADMIN', $user->getRole());
    }

    public function testUserCanHaveFaceImageAfterRegistration(): void
    {
        $user = new Utilisateurs();
        $user->setNom('Benali')
             ->setPrenom('Sara')
             ->setEmail('sara@otemps.fr')
             ->setMotdepasse('hash')
             ->setDateinscription(new \DateTime());

        // Avant la capture FaceID : pas de photo
        $this->assertNull($user->getFaceImage());

        // Après la capture FaceID
        $user->setFaceImage('data:image/png;base64,iVBORw0KGgo=');
        $this->assertNotNull($user->getFaceImage());
        $this->assertStringStartsWith('data:image', $user->getFaceImage());
    }

    public function testSetRoleToAdmin(): void
    {
        $user = new Utilisateurs();
        $user->setRole('ROLE_ADMIN');

        $this->assertSame('ROLE_ADMIN', $user->getRole());
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }
}
