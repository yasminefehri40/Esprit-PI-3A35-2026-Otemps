<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Review;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ReviewController extends AbstractController
{
    #[Route('/event/{id}/review', name: 'app_event_review', methods: ['POST'])]
    public function add(
        Event $event,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $this->getUser();

        if (!$user) {
            $this->addFlash('error', 'Vous devez être connecté pour laisser un avis.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        // Only confirmed participants can leave a review
        $hasConfirmedParticipation = false;
        foreach ($event->getParticipations() as $participation) {
            if ($participation->getUser()->getId() === $user->getId() && $participation->getStatut() === 'confirmée') {
                $hasConfirmedParticipation = true;
                break;
            }
        }

        if (!$hasConfirmedParticipation) {
            $this->addFlash('error', 'Seuls les participants confirmés peuvent laisser un avis.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        // One review per user per event
        foreach ($event->getReviews() as $existingReview) {
            if ($existingReview->getUser()->getId() === $user->getId()) {
                $this->addFlash('warning', 'Vous avez déjà laissé un avis pour cet événement.');
                return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
            }
        }

        $rating = (int) $request->request->get('rating', 0);
        if ($rating < 0 || $rating > 5) {
            $this->addFlash('error', 'La note doit être comprise entre 0 et 5.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $comment = trim($request->request->get('comment', ''));
        if (strlen($comment) < 5) {
            $this->addFlash('error', 'Le commentaire doit contenir au moins 5 caractères.');
            return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
        }

        $review = new Review();
        $review->setEvent($event);
        $review->setUser($user);
        $review->setRating($rating);
        $review->setComment($comment);

        $em->persist($review);
        $em->flush();

        $this->addFlash('success', 'Votre avis a été publié avec succès !');
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId()]);
    }
}
