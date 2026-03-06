<?php

namespace App\DataFixtures;

use App\Entity\Event;
use App\Entity\Utilisateurs;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EventFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        /** @var Utilisateurs $admin */
        $admin = $this->getReference(AppFixtures::ADMIN_USER_REFERENCE);

        // Sample events from database.sql
        $events = [
            [
                'titre' => 'Visite guidée du patrimoine artisanal',
                'description' => 'Découvrez les secrets des artisans d\'autrefois à travers une visite immersive de notre collection d\'objets oubliés. Un voyage dans le temps pour comprendre les savoir-faire ancestraux.',
                'dateDebut' => new \DateTime('2026-03-15 14:00:00'),
                'dateFin' => new \DateTime('2026-03-15 17:00:00'),
                'lieu' => 'Musée OTEMPS, Paris',
            ],
            [
                'titre' => 'Atelier de restauration d\'objets anciens',
                'description' => 'Participez à un atelier pratique où vous apprendrez les techniques de base pour restaurer et préserver les objets patrimoniaux. Encadré par des experts en conservation.',
                'dateDebut' => new \DateTime('2026-03-20 10:00:00'),
                'dateFin' => new \DateTime('2026-03-20 16:00:00'),
                'lieu' => 'Atelier OTEMPS, Lyon',
            ],
            [
                'titre' => 'Conférence : Musique traditionnelle et instruments oubliés',
                'description' => 'Une conférence fascinante sur l\'histoire des instruments de musique traditionnels tombés dans l\'oubli. Avec démonstrations en direct et écoutes d\'enregistrements rares.',
                'dateDebut' => new \DateTime('2026-04-05 18:30:00'),
                'dateFin' => new \DateTime('2026-04-05 20:30:00'),
                'lieu' => 'Auditorium OTEMPS, Marseille',
            ],
            [
                'titre' => 'Visite nocturne : Rituels et symboles culturels',
                'description' => 'Une expérience unique en soirée pour explorer les objets rituels et leur signification dans différentes cultures. Ambiance tamisée et récits captivants.',
                'dateDebut' => new \DateTime('2026-04-12 20:00:00'),
                'dateFin' => new \DateTime('2026-04-12 22:00:00'),
                'lieu' => 'Musée OTEMPS, Paris',
            ],
        ];

        foreach ($events as $eventData) {
            $event = new Event();
            $event->setTitre($eventData['titre']);
            $event->setDescription($eventData['description']);
            $event->setDateDebut($eventData['dateDebut']);
            $event->setDateFin($eventData['dateFin']);
            $event->setLieu($eventData['lieu']);
            $event->setCreator($admin);
            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            AppFixtures::class,
        ];
    }
}
