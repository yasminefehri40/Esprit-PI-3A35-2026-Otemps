<?php

namespace App\Controller;

use App\Repository\EventRepository;
use App\Repository\ParticipationRepository;
use App\Repository\ReviewRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminDashboardController extends AbstractController
{
    #[Route('', name: 'admin_dashboard')]
    public function index(
        EventRepository        $eventRepo,
        UserRepository         $userRepo,
        ParticipationRepository $participationRepo,
        ReviewRepository       $reviewRepo,
    ): Response {
        $statusCounts    = $eventRepo->countByStatut();
        $totalEvents     = array_sum($statusCounts);
        $activeEvents    = $statusCounts['actif'] ?? 0;
        $cancelledEvents = $statusCounts['annulé_météo'] ?? 0;
        $upcomingEvents  = $eventRepo->countUpcoming();

        $totalUsers          = $userRepo->count([]);
        $totalParticipations = $participationRepo->count([]);
        $confirmedParts      = $participationRepo->count(['statut' => 'confirmée']);
        $cancelledParts      = $participationRepo->count(['statut' => 'annulée']);

        $totalReviews = $reviewRepo->count([]);
        $avgRating    = $reviewRepo->getGlobalAverageRating();

        $recentEvents = $eventRepo->findRecent(6);

        return $this->render('admin/dashboard/index.html.twig', [
            'totalEvents'        => $totalEvents,
            'activeEvents'       => $activeEvents,
            'cancelledEvents'    => $cancelledEvents,
            'upcomingEvents'     => $upcomingEvents,
            'totalUsers'         => $totalUsers,
            'totalParticipations'=> $totalParticipations,
            'confirmedParts'     => $confirmedParts,
            'cancelledParts'     => $cancelledParts,
            'totalReviews'       => $totalReviews,
            'avgRating'          => $avgRating,
            'recentEvents'       => $recentEvents,
            'eventCount'         => $totalEvents,
        ]);
    }
}
