<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Form\MediaType;
use App\Repository\MediaRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/medias')]
class MediaController extends AbstractController
{
    #[Route('', name: 'admin_media_index', methods: ['GET'])]
    public function index(MediaRepository $mediaRepository): Response
    {
        return $this->render('admin/media/index.html.twig', [
            'medias' => $mediaRepository->findAll(),
        ]);
    }

    #[Route('/nouveau', name: 'admin_media_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($media);
            $em->flush();
            $this->addFlash('success', 'Média ajouté avec succès.');
            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/new.html.twig', [
            'media' => $media,
            'form' => $form,
        ]);
    }

    #[Route('/{idMedia}', name: 'admin_media_show', methods: ['GET'])]
    public function show(int $idMedia, MediaRepository $mediaRepository): Response
    {
        $media = $mediaRepository->find($idMedia);

        if (!$media) {
            throw $this->createNotFoundException('Média introuvable');
        }

        return $this->render('admin/media/show.html.twig', [
            'media' => $media,
        ]);
    }

    #[Route('/{idMedia}/modifier', name: 'admin_media_edit', methods: ['GET', 'POST'])]
    public function edit(int $idMedia, Request $request, MediaRepository $mediaRepository, EntityManagerInterface $em): Response
    {
        $media = $mediaRepository->find($idMedia);

        if (!$media) {
            throw $this->createNotFoundException('Média introuvable');
        }

        $form = $this->createForm(MediaType::class, $media);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            $this->addFlash('success', 'Média mis à jour.');
            return $this->redirectToRoute('admin_media_show', ['idMedia' => $media->getIdMedia()]);
        }

        return $this->render('admin/media/edit.html.twig', [
            'media' => $media,
            'form' => $form,
        ]);
    }

    #[Route('/{idMedia}/supprimer', name: 'admin_media_delete', methods: ['POST'])]
    public function delete(int $idMedia, Request $request, MediaRepository $mediaRepository, EntityManagerInterface $em): Response
    {
        $media = $mediaRepository->find($idMedia);

        if (!$media) {
            throw $this->createNotFoundException('Média introuvable');
        }

        if ($this->isCsrfTokenValid('delete_media_'.$media->getIdMedia(), $request->request->get('_token'))) {
            $em->remove($media);
            $em->flush();
            $this->addFlash('success', 'Média supprimé.');
        }

        return $this->redirectToRoute('admin_media_index');
    }
}
