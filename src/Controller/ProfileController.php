<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Participation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ProfileController extends AbstractController
{
    #[Route('/profile/{id}/events', name: 'app_profile_events')]
    public function events(User $user): Response
    {
        return $this->render('profile/events.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/profile/participation/{id}/confirm', name: 'app_participation_confirm', methods: ['POST'])]
    public function confirm(Participation $participation, EntityManagerInterface $em): Response
    {
        $participation->setStatut('confirmée');
        $em->flush();

        $this->addFlash('success', 'Participation confirmée !');
        return $this->redirectToRoute('app_profile_events', ['id' => $participation->getUser()->getId()]);
    }

    #[Route('/profile/participation/{id}/cancel', name: 'app_participation_cancel', methods: ['POST'])]
    public function cancel(Participation $participation, EntityManagerInterface $em): Response
    {
        $participation->setStatut('annulée');
        $em->flush();

        $this->addFlash('success', 'Participation annulée.');
        return $this->redirectToRoute('app_profile_events', ['id' => $participation->getUser()->getId()]);
    }
}
