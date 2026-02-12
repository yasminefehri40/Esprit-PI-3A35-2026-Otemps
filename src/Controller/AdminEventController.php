<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/admin/events')]
class AdminEventController extends AbstractController
{
    #[Route('/', name: 'admin_event_index')]
    public function index(Request $request, EventRepository $eventRepository): Response
    {
        $search = $request->query->get('search', '');

        if ($search) {
            $events = $eventRepository->searchEvents($search);
        } else {
            $events = $eventRepository->findBy([], ['dateDebut' => 'DESC']);
        }

        return $this->render('admin/event/index.html.twig', [
            'events' => $events,
            'search' => $search,
        ]);
    }

    #[Route('/new', name: 'admin_event_new')]
    public function new(Request $request, EntityManagerInterface $em, UserRepository $userRepository, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $creator = $userRepository->find(1); // Static admin user

            $event = new Event();
            $event->setTitre($request->request->get('titre'));
            $event->setDescription($request->request->get('description'));

            try {
                $event->setDateDebut(new \DateTime($request->request->get('dateDebut')));
                $event->setDateFin(new \DateTime($request->request->get('dateFin')));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Format de date invalide.');
                return $this->render('admin/event/new.html.twig', [
                    'errors' => ['dates' => 'Format de date invalide.'],
                    'formData' => $request->request->all()
                ]);
            }

            $event->setLieu($request->request->get('lieu'));
            $event->setCreator($creator);

            // Validate the entity
            $errors = $validator->validate($event);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }

                return $this->render('admin/event/new.html.twig', [
                    'errors' => $errorMessages,
                    'formData' => $request->request->all()
                ]);
            }

            $em->persist($event);
            $em->flush();

            $this->addFlash('success', 'Événement créé avec succès !');
            return $this->redirectToRoute('admin_event_index');
        }

        return $this->render('admin/event/new.html.twig');
    }

    #[Route('/{id}/edit', name: 'admin_event_edit')]
    public function edit(Event $event, Request $request, EntityManagerInterface $em, ValidatorInterface $validator): Response
    {
        if ($request->isMethod('POST')) {
            $event->setTitre($request->request->get('titre'));
            $event->setDescription($request->request->get('description'));

            try {
                $event->setDateDebut(new \DateTime($request->request->get('dateDebut')));
                $event->setDateFin(new \DateTime($request->request->get('dateFin')));
            } catch (\Exception $e) {
                $this->addFlash('error', 'Format de date invalide.');
                return $this->render('admin/event/edit.html.twig', [
                    'event' => $event,
                    'errors' => ['dates' => 'Format de date invalide.'],
                    'formData' => $request->request->all()
                ]);
            }

            $event->setLieu($request->request->get('lieu'));

            // Validate the entity
            $errors = $validator->validate($event);

            if (count($errors) > 0) {
                $errorMessages = [];
                foreach ($errors as $error) {
                    $errorMessages[$error->getPropertyPath()] = $error->getMessage();
                }

                return $this->render('admin/event/edit.html.twig', [
                    'event' => $event,
                    'errors' => $errorMessages,
                    'formData' => $request->request->all()
                ]);
            }

            $em->flush();

            $this->addFlash('success', 'Événement modifié avec succès !');
            return $this->redirectToRoute('admin_event_index');
        }

        return $this->render('admin/event/edit.html.twig', [
            'event' => $event,
        ]);
    }



    #[Route('/{id}/delete', name: 'admin_event_delete', methods: ['POST'])]
    public function delete(Event $event, EntityManagerInterface $em): Response
    {
        $em->remove($event);
        $em->flush();

        $this->addFlash('success', 'Événement supprimé avec succès !');
        return $this->redirectToRoute('admin_event_index');
    }

    #[Route('/{id}/participants', name: 'admin_event_participants')]
    public function participants(Event $event): Response
    {
        return $this->render('admin/event/participants.html.twig', [
            'event' => $event,
        ]);
    }
}
