<?php
// src/Controller/TestPHPMailerController.php

namespace App\Controller;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestPHPMailerController extends AbstractController
{
    #[Route('/test-phpmailer', name: 'test_phpmailer')]
    public function testMail(): Response
    {
        $toEmail = 'khemirichedi121@gmail.com';
        $toName = 'Hsini';
        $verificationCode = random_int(100000, 999999); // code à 6 chiffres

        $mail = new PHPMailer(true);

        try {
            // Configuration SMTP (Gmail exemple)
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'hsinigaylen@gmail.com'; // ton email Gmail
            $mail->Password   = 'avmj ewhn ikxv prbm';         // mot de passe d'application Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Expéditeur & destinataire
            $mail->setFrom('no-reply@monsite.com', 'Mon Site');
            $mail->addAddress($toEmail, $toName);

            // Contenu
            $mail->isHTML(true);
            $mail->Subject = 'Code de vérification';
            $mail->Body    = "<p>Bonjour {$toName},</p><p>Votre code de vérification est : <b>{$verificationCode}</b></p>";

            $mail->send();

            return new Response("Mail envoyé avec succès ! Code : {$verificationCode}");
        } catch (Exception $e) {
            return new Response("Erreur PHPMailer : " . $mail->ErrorInfo);
        }
    }
}
