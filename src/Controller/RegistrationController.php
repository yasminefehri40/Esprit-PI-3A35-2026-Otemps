<?php

namespace App\Controller;

use App\Entity\Utilisateurs;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\{TextType, EmailType, PasswordType, SubmitType};
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Form\FormError;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $hasher, EntityManagerInterface $em): Response
    {
        $form = $this->createFormBuilder()
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 50])
                ]
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Length(['min' => 2, 'max' => 50])
                ]
            ])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('motdepasse', PasswordType::class, ['label' => 'Mot de passe', 'mapped' => false])
            ->add('register', SubmitType::class, ['label' => "S'inscrire"])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            $emailData = $form->get('email')->getData();
            $plainPassword = $form->get('motdepasse')->getData();

            $existingUser = $em->getRepository(Utilisateurs::class)
                ->findOneBy(['email' => $emailData]);

            if ($existingUser) {
                $form->get('email')->addError(new FormError('Cette adresse email est déjà utilisée.'));
            }

            if ($form->isValid()) {

                $hashedPassword = $hasher->hashPassword(new Utilisateurs(), $plainPassword);
                $verificationCode = random_int(100000, 999999);

                $request->getSession()->set('pending_user', [
                    'nom' => $form->get('nom')->getData(),
                    'prenom' => $form->get('prenom')->getData(),
                    'email' => $emailData,
                    'motdepasse' => $hashedPassword,
                    'code' => $verificationCode
                ]);

                // PHPMailer
                $mail = new PHPMailer(true);

                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'hsinigaylen@gmail.com';
                    $mail->Password   = 'avmj ewhn ikxv prbm';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;

                    $mail->setFrom('no-reply@monsite.com', 'Mon Site');
                    $mail->addAddress($emailData, $form->get('prenom')->getData());

                    $mail->isHTML(true);
                    $mail->Subject = 'Code de vérification pour votre compte';
                    $mail->Body = "
                        <p>Bonjour {$form->get('prenom')->getData()},</p>
                        <p>Votre code de vérification est : <b>$verificationCode</b></p>
                    ";

                    $mail->send();

                } catch (Exception $e) {
                    $this->addFlash('error', "Impossible d'envoyer le mail : " . $mail->ErrorInfo);
                    return $this->redirectToRoute('app_register');
                }

                $this->addFlash('success', 'Un code de vérification a été envoyé à votre email.');
                return $this->redirectToRoute('app_verify_code');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    #[Route('/verify-code', name: 'app_verify_code')]
    public function verifyCode(Request $request): Response
    {
        $form = $this->createFormBuilder()
            ->add('code', TextType::class, ['label' => 'Code de vérification'])
            ->add('verify', SubmitType::class, ['label' => 'Vérifier'])
            ->getForm();

        $form->handleRequest($request);

        $sessionData = $request->getSession()->get('pending_user');

        if ($form->isSubmitted() && $form->isValid() && $sessionData) {

            $inputCode = $form->get('code')->getData();

            if ($inputCode == $sessionData['code']) {
                return $this->redirectToRoute('app_register_face');
            } else {
                $this->addFlash('error', 'Code de vérification incorrect.');
            }
        }

        return $this->render('registration/verify_code.html.twig', [
            'verifyForm' => $form->createView()
        ]);
    }

    #[Route('/register-face', name: 'app_register_face')]
    public function registerFace(Request $request, EntityManagerInterface $em): Response
    {
        $sessionData = $request->getSession()->get('pending_user');

        if (!$sessionData) {
            $this->addFlash('error', 'Aucune inscription en cours.');
            return $this->redirectToRoute('app_register');
        }

        $form = $this->createFormBuilder()
            ->add('photo', TextType::class, [
                'label' => 'Capture Faciale',
                'mapped' => false,
                'attr' => ['hidden' => true]
            ])
            ->add('save', SubmitType::class, ['label' => 'Valider'])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $photoBase64 = $form->get('photo')->getData();

            if (!$photoBase64) {
                $this->addFlash('error', 'Veuillez capturer votre visage.');
                return $this->redirectToRoute('app_register_face');
            }

            // 1️⃣ Enregistrer image temporaire
            $tempFile = tempnam(sys_get_temp_dir(), 'face_') . '.png';
            $photoData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $photoBase64));
            file_put_contents($tempFile, $photoData);

            // 2️⃣ Appeler Python avec le chemin du fichier
            $python = "python"; // ou chemin complet python.exe
            $script = $this->getParameter('kernel.project_dir') . "/public/python/register_face.py";
            $command = escapeshellcmd("$python $script $tempFile");

            $filename = trim(shell_exec($command));

            // supprimer fichier temporaire
            unlink($tempFile);

            if ($filename == "NO_FACE" || empty($filename)) {
                $this->addFlash('error', 'Aucun visage détecté.');
                return $this->redirectToRoute('app_register_face');
            }

            // 3️⃣ Enregistrer l'utilisateur
            $user = new Utilisateurs();
            $user->setNom($sessionData['nom']);
            $user->setPrenom($sessionData['prenom']);
            $user->setEmail($sessionData['email']);
            $user->setMotdepasse($sessionData['motdepasse']);
            $user->setDateinscription(new \DateTime());
            $user->setRole('ROLE_USER');
            $user->setFaceImage($filename);

            $em->persist($user);
            $em->flush();

            $request->getSession()->remove('pending_user');

            $this->addFlash('success', 'Votre compte a été créé avec reconnaissance faciale !');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register_face.html.twig', [
            'faceForm' => $form->createView()
        ]);
    }
}
