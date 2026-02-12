<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Participation;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class EventController extends AbstractController
{
    #[Route('/event/{id}', name: 'app_event_show')]
    public function show(Event $event): Response
    {
        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/event/{id}/participate', name: 'app_event_participate', methods: ['POST'])]
    public function participate(
        Event $event,
        Request $request,
        EntityManagerInterface $em,
        UserRepository $userRepository
    ): Response {
        $userId = $request->request->get('user_id');
        $user = $userRepository->find($userId);

        if (!$user) {
            $this->addFlash('error', 'Utilisateur non trouvé.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        // Check if already registered with confirmed status
        foreach ($event->getParticipations() as $participation) {
            if ($participation->getUser()->getId() === $user->getId() && $participation->getStatut() === 'confirmée') {
                $this->addFlash('warning', 'Vous êtes déjà inscrit à cet événement.');
                return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
            }
        }

        $participation = new Participation();
        $participation->setEvent($event);
        $participation->setUser($user);
        $participation->setStatut('confirmée');

        $em->persist($participation);
        $em->flush();

        $this->addFlash('success', 'Votre inscription a été confirmée !');
        return $this->redirectToRoute('app_profile_events', ['id' => $user->getId()]);
    }
}
