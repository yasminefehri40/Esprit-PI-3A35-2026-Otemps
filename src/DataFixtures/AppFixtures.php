<?php

namespace App\DataFixtures;

use App\Entity\Utilisateurs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new Utilisateurs();
        $admin->setNom('Admin');
        $admin->setPrenom('OTEMPS');
        $admin->setEmail('admin@otemps.fr');
        $admin->setMotdepasse('$2y$13$placeholder_hash_for_fixtures');
        $admin->setDateinscription(new \DateTime());
        $admin->setRole('ROLE_ADMIN');
        $manager->persist($admin);

        // Add reference for use in EventFixtures
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        // Create regular users
        $users = [
            ['nom' => 'Martin', 'prenom' => 'Alice', 'email' => 'alice.martin@example.com'],
            ['nom' => 'Dupont', 'prenom' => 'Bob', 'email' => 'bob.dupont@example.com'],
            ['nom' => 'Bernard', 'prenom' => 'Claire', 'email' => 'claire.bernard@example.com'],
            ['nom' => 'Rousseau', 'prenom' => 'David', 'email' => 'david.rousseau@example.com'],
        ];

        foreach ($users as $userData) {
            $user = new Utilisateurs();
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setEmail($userData['email']);
            $user->setMotdepasse('$2y$13$placeholder_hash_for_fixtures');
            $user->setDateinscription(new \DateTime());
            $user->setRole('ROLE_USER');
            $manager->persist($user);
        }

        $manager->flush();
    }
}
