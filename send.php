<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/PHPMailer/src/Exception.php';
require __DIR__ . '/PHPMailer/src/PHPMailer.php';
require __DIR__ . '/PHPMailer/src/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit;
}

/* ===== Anti-spam honeypot ===== */
if (!empty($_POST['website'])) {
    // Bot détecté → on ne fait rien
    header('Location: merci.html');
    exit;
}

/* ===== Sécurisation des champs ===== */
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$subject = trim($_POST['subject'] ?? 'Demande via le site');
$date    = trim($_POST['event_date'] ?? 'Non précisée');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    exit('Champs obligatoires manquants.');
}

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp-fr.securemail.pro';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'contact@lechatelier-creatif.fr';
    $mail->Password   = 'XXXX'; // mot de passe exact Amen
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    $mail->setFrom('contact@lechatelier-creatif.fr', "Le Cha'Telier Créatif");
    $mail->addAddress('contact@lechatelier-creatif.fr');
    $mail->addReplyTo($email, $name);

    $mail->isHTML(true);
    $mail->Subject = "📩 Nouveau message – {$subject}";
    $mail->Body = "
        <strong>Nom :</strong> {$name}<br>
        <strong>Email :</strong> {$email}<br>
        <strong>Type de demande :</strong> {$subject}<br>
        <strong>Date événement :</strong> {$date}<br><br>
        <strong>Message :</strong><br>
        " . nl2br(htmlspecialchars($message));

    $mail->send();

    header('Location: merci.html');
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo 'Erreur d’envoi : ' . $mail->ErrorInfo;
}
