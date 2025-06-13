<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Make sure Composer is installed and PHPMailer is required

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $mobile = $_POST['mobile'];
    $age = $_POST['age'];

    // Files
    $aadhar = $_FILES['aadharCard'];
    $bank = $_FILES['bankPassbook'];
    $electricity = $_FILES['electricityBill'];
    $income = $_FILES['incomeCertificate'];
    $photo = $_FILES['passportPhoto'];

    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'patrasagarika654@gmail.com';
        $mail->Password = 'yder qkfe hbng lfcj'; // App password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        // Email content
        $mail->setFrom('patrasagarika654@gmail.com', 'Form Submission');
        $mail->addAddress('patrasagarika654@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'New PM Surya Ghar Form Submission';
        $mail->Body = "
            <h3>New Application Received</h3>
            <p><strong>Full Name:</strong> $fullName</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Mobile:</strong> $mobile</p>
            <p><strong>Age:</strong> $age</p>
        ";

        // Attachments
        $mail->addAttachment($aadhar['tmp_name'], 'AadharCard_' . $aadhar['name']);
        $mail->addAttachment($bank['tmp_name'], 'BankPassbook_' . $bank['name']);
        $mail->addAttachment($electricity['tmp_name'], 'ElectricityBill_' . $electricity['name']);
        $mail->addAttachment($income['tmp_name'], 'IncomeCertificate_' . $income['name']);
        $mail->addAttachment($photo['tmp_name'], 'PassportPhoto_' . $photo['name']);

        $mail->send();
        echo json_encode(['success' => true]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $mail->ErrorInfo]);
    }
}
?>
