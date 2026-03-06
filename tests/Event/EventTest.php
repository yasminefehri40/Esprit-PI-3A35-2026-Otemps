<?php

namespace App\Tests\Event;

use App\Entity\Event;
use App\Entity\Utilisateurs;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    private function createValidEvent(): Event
    {
        $creator = new Utilisateurs();
        $creator->setNom('Admin')
                ->setPrenom('Test')
                ->setEmail('admin@otemps.fr')
                ->setMotdepasse('hash')
                ->setDateinscription(new \DateTime());

        $event = new Event();
        $event->setTitre('Festival du Patrimoine')
              ->setDescription('Un événement dédié à la valorisation du patrimoine culturel tunisien.')
              ->setDateDebut(new \DateTime('+1 month'))
              ->setDateFin(new \DateTime('+1 month +3 days'))
              ->setLieu('Tunis, Médina')
              ->setNbPlaces(200)
              ->setCreator($creator);

        return $event;
    }

    public function testEventCreationWithValidData(): void
    {
        $event = $this->createValidEvent();

        $this->assertSame('Festival du Patrimoine', $event->getTitre());
        $this->assertSame('Tunis, Médina', $event->getLieu());
        $this->assertSame(200, $event->getNbPlaces());
        $this->assertNotNull($event->getCreator());
    }

    public function testDefaultStatutIsActif(): void
    {
        $event = new Event();

        $this->assertSame('actif', $event->getStatut());
    }

    public function testSetStatut(): void
    {
        $event = $this->createValidEvent();
        $event->setStatut('annulé');

        $this->assertSame('annulé', $event->getStatut());
        $this->assertTrue($event->isAnnule());
    }

    public function testIsAnnuleReturnsFalseForActif(): void
    {
        $event = $this->createValidEvent();

        $this->assertFalse($event->isAnnule());
    }

    public function testNewEventHasNoParticipations(): void
    {
        $event = new Event();

        $this->assertCount(0, $event->getParticipations());
        $this->assertSame(0, $event->getParticipantsCount());
    }

    public function testNewEventHasNoReviews(): void
    {
        $event = new Event();

        $this->assertCount(0, $event->getReviews());
        $this->assertSame(0, $event->getReviewsCount());
        $this->assertNull($event->getAverageRating());
    }

    public function testDateFinIsAfterDateDebut(): void
    {
        $event = $this->createValidEvent();

        $this->assertGreaterThan(
            $event->getDateDebut()->getTimestamp(),
            $event->getDateFin()->getTimestamp()
        );
    }

    public function testNewEventHasNoId(): void
    {
        $event = new Event();

        $this->assertNull($event->getId());
    }
}
