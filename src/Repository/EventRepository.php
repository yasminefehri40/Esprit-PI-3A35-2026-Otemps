<?php

namespace App\Repository;

use App\Entity\Event;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function findActiveEvents(): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Active events starting within the next $days days.
     * Pass null to get all future active events with no upper date limit.
     */
    public function findUpcomingActiveEvents(int $days = 5): array
    {
        $now   = new \DateTime();
        $limit = (new \DateTime())->modify("+{$days} days");

        return $this->createQueryBuilder('e')
            ->where('e.statut = :statut')
            ->andWhere('e.dateDebut BETWEEN :now AND :limit')
            ->setParameter('statut', 'actif')
            ->setParameter('now', $now)
            ->setParameter('limit', $limit)
            ->orderBy('e.dateDebut', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function searchEvents(string $search): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.titre LIKE :search')
            ->orWhere('e.description LIKE :search')
            ->orWhere('e.lieu LIKE :search')
            ->setParameter('search', '%' . $search . '%')
            ->orderBy('e.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatut(): array
    {
        $rows = $this->createQueryBuilder('e')
            ->select('e.statut, COUNT(e.id) as total')
            ->groupBy('e.statut')
            ->getQuery()
            ->getResult();

        $result = ['actif' => 0, 'annulé_météo' => 0];
        foreach ($rows as $row) {
            $result[$row['statut']] = (int) $row['total'];
        }
        return $result;
    }

    public function countUpcoming(): int
    {
        return (int) $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->where('e.statut = :statut')
            ->andWhere('e.dateDebut > :now')
            ->setParameter('statut', 'actif')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findRecent(int $limit = 6): array
    {
        return $this->createQueryBuilder('e')
            ->orderBy('e.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
