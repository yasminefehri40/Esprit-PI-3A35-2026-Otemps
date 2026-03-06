<?php

namespace App\Tests\Event;

use App\Entity\Event;
use PHPUnit\Framework\TestCase;

/**
 * Tests sur la gestion du statut et des places disponibles d'un événement.
 */
class EventStatutTest extends TestCase
{
    private function makeEvent(int $nbPlaces): Event
    {
        $event = new Event();
        $event->setTitre('Concert Malouf')
              ->setDescription('Soirée musicale dédiée au patrimoine musical tunisien.')
              ->setLieu('Carthage')
              ->setNbPlaces($nbPlaces)
              ->setDateDebut(new \DateTime('+2 months'))
              ->setDateFin(new \DateTime('+2 months +1 day'));

        return $event;
    }

    public function testPlacesRestantesEqualsNbPlacesWhenNoParticipants(): void
    {
        $event = $this->makeEvent(100);

        // Sans participation confirmée, toutes les places sont libres
        $this->assertSame(100, $event->getPlacesRestantes());
    }

    public function testPlacesRestantesIsNeverNegative(): void
    {
        $event = $this->makeEvent(10);

        // getParticipantsCount retourne 0 si pas de participation,
        // donc getPlacesRestantes = max(0, 10 - 0) = 10
        $this->assertGreaterThanOrEqual(0, $event->getPlacesRestantes());
    }

    public function testStatutTransitions(): void
    {
        $event = $this->makeEvent(50);

        $this->assertSame('actif', $event->getStatut());

        $event->setStatut('complet');
        $this->assertSame('complet', $event->getStatut());
        $this->assertFalse($event->isAnnule());

        $event->setStatut('annulé pour raisons techniques');
        $this->assertTrue($event->isAnnule());
    }

    public function testIsAnnuleDetectsAnnulePrefix(): void
    {
        $event = $this->makeEvent(50);

        $event->setStatut('annulé');
        $this->assertTrue($event->isAnnule());

        $event->setStatut('annulé pour météo');
        $this->assertTrue($event->isAnnule());

        $event->setStatut('actif');
        $this->assertFalse($event->isAnnule());
    }

    public function testNbPlacesCanBeUpdated(): void
    {
        $event = $this->makeEvent(50);
        $event->setNbPlaces(150);

        $this->assertSame(150, $event->getNbPlaces());
        $this->assertSame(150, $event->getPlacesRestantes());
    }

    public function testGetParticipantsCountIsZeroWithoutParticipations(): void
    {
        $event = $this->makeEvent(30);

        $this->assertSame(0, $event->getParticipantsCount());
    }

    public function testReviewsCountIsZeroWithoutReviews(): void
    {
        $event = $this->makeEvent(30);

        $this->assertSame(0, $event->getReviewsCount());
    }

    public function testAverageRatingIsNullWithoutReviews(): void
    {
        $event = $this->makeEvent(30);

        $this->assertNull($event->getAverageRating());
    }
}
