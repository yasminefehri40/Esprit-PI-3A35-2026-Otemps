<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\FormLoginAuthenticator;
use Symfony\Component\Form\Extension\Core\Type\{EmailType, PasswordType, TextType};
use Symfony\Component\Validator\Constraints as Assert;
use PHPMailer\PHPMailer\PHPMailer;

class SecurityController extends AbstractController
{
    // Connexion classique (sans CSRF, simple)
    #[Route('/login', name: 'app_login', methods: ['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    // Connexion FaceID
    #[Route('/face-login', name: 'app_face_login', methods:['POST'])]
    public function faceLogin(Request $request, EntityManagerInterface $em, UserAuthenticatorInterface $userAuthenticator, FormLoginAuthenticator $formLoginAuthenticator): JsonResponse
    {
        $uploadedFile = $request->files->get('face_image');
        if (!$uploadedFile) return new JsonResponse(['success'=>false,'message'=>'Aucune image reçue']);

        $uploadedPath = $uploadedFile->getPathname();
        $uploadedImg = @imagecreatefrompng($uploadedPath) ?: @imagecreatefromjpeg($uploadedPath);
        if (!$uploadedImg) return new JsonResponse(['success'=>false,'message'=>'Image invalide']);

        $users = $em->getRepository(Utilisateurs::class)->findAll();

        foreach($users as $user){
            if(!$user->getFaceImage()) continue;

            $storedPath = $this->getParameter('kernel.project_dir').'/public/faces/'.$user->getFaceImage();
            if(!file_exists($storedPath)) continue;

            $storedImg = @imagecreatefrompng($storedPath) ?: @imagecreatefromjpeg($storedPath);
            if(!$storedImg) continue;

            // Comparaison simple : pixel central
            $w1 = imagesx($uploadedImg); $h1 = imagesy($uploadedImg);
            $w2 = imagesx($storedImg); $h2 = imagesy($storedImg);

            $color1 = imagecolorat($uploadedImg,intval($w1/2),intval($h1/2));
            $color2 = imagecolorat($storedImg,intval($w2/2),intval($h2/2));

            if(abs($color1-$color2) < 500000){
                // Connexion Symfony
                $userAuthenticator->authenticateUser($user, $formLoginAuthenticator, $request);
                return new JsonResponse([
                    'success'=>true,
                    'email'=>$user->getEmail(),
                    'redirect'=>$this->generateUrl('app_welcome')
                ]);
            }
        }

        return new JsonResponse(['success'=>false,'message'=>'FaceID non reconnu']);
    }

    // Redirect after login based on role
    #[Route('/after-login', name:'app_after_login')]
    public function afterLogin(): Response
    {
        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_welcome');
        }
        return $this->redirectToRoute('app_user_home');
    }

    #[Route('/accueil', name:'app_user_home')]
    public function userHome(): Response
    {
        $user = $this->getUser();
        if (!$user) return $this->redirectToRoute('app_login');
        return $this->render('security/user_home.html.twig', [
            'user' => $user,
        ]);
    }

    // Page après connexion
    #[Route('/welcome', name:'app_welcome')]
    public function welcome(): Response
    {
        $user = $this->getUser();
        if(!$user) return $this->redirectToRoute('app_login');

        return $this->render('security/welcome.html.twig',['email'=>$user->getEmail()]);
    }

    // Déconnexion
    #[Route('/logout', name:'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('Cette méthode sera interceptée par le firewall.');
    }

    // Mot de passe oublié
    #[Route('/forgot-password', name: 'app_forgot_password')]
public function forgotPassword(Request $request, EntityManagerInterface $em): Response
{
    // Création du formulaire
    $form = $this->createFormBuilder(null, ['csrf_protection' => false])
        ->add('email', EmailType::class, [
            'label' => 'Votre email',
            'constraints' => [
                new Assert\NotBlank(['message' => 'Veuillez saisir votre email.']),
                new Assert\Email(['message' => 'Email invalide.'])
            ]
        ])
        ->getForm();

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $email = $form->get('email')->getData();

        // Vérification de l'existence de l'utilisateur
        $user = $em->getRepository(Utilisateurs::class)->findOneBy(['email' => $email]);
        if (!$user) {
            $this->addFlash('error', "Cet email n'est pas enregistré.");
            return $this->redirectToRoute('app_forgot_password');
        }

        // Génération du code de réinitialisation
        $code = random_int(100000, 999999);
        $request->getSession()->set('reset_password', [
            'email' => $email,
            'code' => $code
        ]);

        // Envoi du mail via SMTP
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'hsinigaylen@gmail.com';
            $mail->Password = 'avmj ewhn ikxv prbm';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            $mail->setFrom('no-reply@monsite.com', 'Mon Site');
            $mail->addAddress($email, $user->getPrenom());
            $mail->isHTML(true);
            $mail->Subject = 'Code de réinitialisation';
            $mail->Body = "<p>Bonjour {$user->getPrenom()},</p><p>Voici votre code de réinitialisation : <b>$code</b></p>";

            $mail->send();

            $this->addFlash('success', 'Un code a été envoyé à votre email.');
            return $this->redirectToRoute('app_reset_password');

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            $this->addFlash('error', "Impossible d'envoyer le mail : " . $mail->ErrorInfo);
        }
    }

    return $this->render('security/forgot_password.html.twig', [
        'forgotForm' => $form->createView()
    ]);
}

    // Reset password
    #[Route('/reset-password', name:'app_reset_password')]
    public function resetPassword(Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
    {
        $session = $request->getSession()->get('reset_password');
        if(!$session){
            $this->addFlash('error','Aucune demande en cours.');
            return $this->redirectToRoute('app_forgot_password');
        }

        $form = $this->createFormBuilder(null, ['csrf_protection' => false])
            ->add('code', TextType::class,['label'=>'Code reçu par email'])
            ->add('newPassword', PasswordType::class,['label'=>'Nouveau mot de passe'])
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            if($form->get('code')->getData() != $session['code']){
                $this->addFlash('error','Code incorrect.');
            } else {
                $user = $em->getRepository(Utilisateurs::class)->findOneBy(['email'=>$session['email']]);
                if(!$user){
                    $this->addFlash('error','Utilisateur introuvable.');
                    return $this->redirectToRoute('app_forgot_password');
                }

                $user->setMotdepasse($hasher->hashPassword($user,$form->get('newPassword')->getData()));
                $em->flush();
                $request->getSession()->remove('reset_password');
                $this->addFlash('success','Mot de passe réinitialisé !');
                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig',['resetForm'=>$form->createView()]);
    }
}
