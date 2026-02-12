<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public const ADMIN_USER_REFERENCE = 'admin-user';

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $admin = new User();
        $admin->setName('Admin OTEMPS');
        $admin->setEmail('admin@otemps.fr');
        $admin->setIsAdmin(true);
        $manager->persist($admin);

        // Add reference for use in EventFixtures
        $this->addReference(self::ADMIN_USER_REFERENCE, $admin);

        // Create regular users
        $users = [
            ['name' => 'Alice Martin', 'email' => 'alice.martin@example.com'],
            ['name' => 'Bob Dupont', 'email' => 'bob.dupont@example.com'],
            ['name' => 'Claire Bernard', 'email' => 'claire.bernard@example.com'],
            ['name' => 'David Rousseau', 'email' => 'david.rousseau@example.com'],
        ];

        foreach ($users as $userData) {
            $user = new User();
            $user->setName($userData['name']);
            $user->setEmail($userData['email']);
            $user->setIsAdmin(false);
            $manager->persist($user);
        }

        $manager->flush();
    }
}
