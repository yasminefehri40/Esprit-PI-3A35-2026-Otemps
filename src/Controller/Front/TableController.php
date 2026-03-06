<?php

namespace App\Controller\Front;

use App\Repository\CategorieRepository;
use App\Repository\ObjetRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/patrimoine', name: 'front_')]
class TableController extends AbstractController
{
    #[Route('', name: 'home')]
    public function index(CategorieRepository $categorieRepo): Response
    {
        $categories = $categorieRepo->findAll();

        return $this->render('front/home.html.twig', [
            'categories' => $categories,
        ]);
    }

    #[Route('/categorie/{idCategorie}', name: 'category_show')]
    public function showCategory(int $idCategorie, CategorieRepository $categorieRepo, ObjetRepository $objetRepo): Response
    {
        $categorie = $categorieRepo->find($idCategorie);

        if (!$categorie) {
            throw $this->createNotFoundException('Catégorie non trouvée');
        }

        $objets = $objetRepo->findBy(['categorie' => $categorie]);

        return $this->render('front/table/show.html.twig', [
            'categorie' => $categorie,
            'objets' => $objets,
        ]);
    }

    #[Route('/objet/{idObjet}', name: 'objet_show')]
    public function showObjet(int $idObjet, ObjetRepository $objetRepo): Response
    {
        $objet = $objetRepo->find($idObjet);

        if (!$objet) {
            throw $this->createNotFoundException('Objet introuvable');
        }

        return $this->render('front/objet/show.html.twig', [
            'objet' => $objet,
        ]);
    }
}
