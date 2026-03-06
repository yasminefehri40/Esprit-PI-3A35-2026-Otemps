<?php

namespace App\Controller\Admin;

use App\Entity\Utilisateurs;
use App\Form\UtilisateurType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/utilisateurs')]
class UserController extends AbstractController
{
    private EntityManagerInterface $em;
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(EntityManagerInterface $em, UserPasswordHasherInterface $passwordHasher)
    {
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    // ================= LISTE DES UTILISATEURS =================
    #[Route('/', name: 'admin_user_index')]
    public function index(): Response
    {
        $users = $this->em->getRepository(Utilisateurs::class)->findAll();

        return $this->render('admin/user/index.html.twig', [
            'users' => $users,
        ]);
    }

    // ================= MODIFIER UN UTILISATEUR =================
    #[Route('/edit/{id}', name: 'admin_user_edit')]
    public function edit(Request $request, Utilisateurs $user): Response
    {
        $form = $this->createForm(UtilisateurType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion du mot de passe
            $plainPassword = $form->get('motdepasse')->getData();
            if ($plainPassword) {
                $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
                $user->setMotdepasse($hashedPassword);
            }

            $this->em->flush();
            $this->addFlash('success', 'Utilisateur mis à jour avec succès.');
            return $this->redirectToRoute('admin_user_index');
        }

        return $this->render('admin/user/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    // ================= SUPPRIMER UN UTILISATEUR =================
    #[Route('/delete/{id}', name: 'admin_user_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateurs $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $this->em->remove($user);
            $this->em->flush();
            $this->addFlash('success', 'Utilisateur supprimé avec succès.');
        }

        return $this->redirectToRoute('admin_user_index');
    }

    #[Route('/search', name: 'admin_user_search', methods: ['GET'])]
public function search(Request $request): Response
{
    $search = $request->query->get('search');

    $users = $this->em->getRepository(Utilisateurs::class)
        ->createQueryBuilder('u')
        ->where('u.nom LIKE :search')
        ->setParameter('search', '%' . $search . '%')
        ->getQuery()
        ->getResult();

    return $this->render('admin/user/index.html.twig', [
        'users' => $users,
        'search' => $search,
    ]);
}
#[Route('/sort/{order}', name: 'admin_user_sort', requirements: ['order' => 'az|za'])]
public function sort(string $order): Response
{
    $direction = $order === 'az' ? 'ASC' : 'DESC';

    $users = $this->em->getRepository(Utilisateurs::class)
        ->createQueryBuilder('u')
        ->orderBy('u.nom', $direction)
        ->getQuery()
        ->getResult();

    return $this->render('admin/user/index.html.twig', [
        'users' => $users,
        'sort' => $order,
    ]);
}
#[Route('/new', name: 'admin_user_new')]
public function new(
    Request $request,
    UserPasswordHasherInterface $passwordHasher
): Response {
    $user = new Utilisateurs();
    $user->setDateinscription(new \DateTime());
    $user->setRole('ROLE_USER');

    $form = $this->createForm(UtilisateurType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

        // mot de passe
        $plainPassword = $form->get('motdepasse')->getData();
        if ($plainPassword) {
            $user->setMotdepasse(
                $passwordHasher->hashPassword($user, $plainPassword)
            );
        }

        // rôle (champ non mappé)
        $role = $form->get('role')->getData();
        $user->setRole($role);

        $this->em->persist($user);
        $this->em->flush();

        $this->addFlash('success', 'Utilisateur ajouté avec succès');
        return $this->redirectToRoute('admin_user_index');
    }

    return $this->render('admin/user/new.html.twig', [
        'form' => $form->createView(),
    ]);
}


}
