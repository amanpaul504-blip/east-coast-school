<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../res/includes/mail_config.php';
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;

    $mailConfig = getMailConfig();
    if ($mailConfig['username'] === '' || $mailConfig['password'] === '') {
        throw new RuntimeException('Email is not configured. Add local mail credentials to appl/.env.');
    }
    $mail->Username = $mailConfig['username'];
    $mail->Password = $mailConfig['password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Sender
    $mail->setFrom($mailConfig['username'], $mailConfig['from_name']);

    // Applicant's email from the form
    $mail->addAddress($email, $firstname . ' ' . $lastname);

    // Attach generated PDF (optional)
    if (file_exists($pdfFile)) {
        $mail->addAttachment($pdfFile);
    }

    // Email content
    $mail->isHTML(true);
    $mail->Subject = 'School donation pledge received';

    $mail->Body = "
    Dear <b>$fname</b>,<br><br>

    Thank you for your support to East Coast Secondary School.<br><br>

    We have successfully received your donation pledge and it is now under review.<br><br>

    Regards,<br>
    East Coast Secondary School Administration
    ";

    $mail->AltBody = "Dear $fname, Your application has been received successfully.";

    $mail->send();

} catch (Exception $e) {
    error_log("Mailer Error: " . $mail->ErrorInfo);
}
