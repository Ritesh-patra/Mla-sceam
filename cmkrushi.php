<?php
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';
require 'PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $mail = new PHPMailer(true);

    try {
        // SMTP config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'patrasagarika654@gmail.com'; // Gmail
        $mail->Password   = 'yder qkfe hbng lfcj'; // App Password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        // Sender/Recipient
        $mail->setFrom('patrasagarika654@gmail.com', 'Poultry Form');
        $mail->addAddress('patrasagarika654@gmail.com');

        // Subject and HTML body
        $mail->isHTML(true);
        $mail->Subject = 'New Poultry Application';

        // Form data body
        $body = "
            <h3>Applicant Details:</h3>
            <p><strong>Full Name:</strong> {$_POST['full_name']}</p>
            <p><strong>Father/Husband's Name:</strong> {$_POST['father_husband_name']}</p>
            <p><strong>Mobile Number:</strong> {$_POST['mobile_number']}</p>
            <p><strong>Aadhaar Number:</strong> {$_POST['aadhaar_number']}</p>
            <p><strong>Bank Account Number:</strong> {$_POST['bank_account_number']}</p>
            <p><strong>Bank IFSC Code:</strong> {$_POST['bank_ifsc_code']}</p>
            <p><strong>Project Address:</strong> " . nl2br($_POST['project_location_address']) . "</p>
        ";
        $mail->Body = $body;

        // Attachments
        $fields = [
            'land_ownership_docs', 'lease_agreement', 'caste_certificate',
            'disability_certificate', 'bpl_ration_card', 'farmer_id_card',
            'bank_passbook'
        ];

        foreach ($fields as $field) {
            if (!empty($_FILES[$field]['name'])) {
                $mail->addAttachment($_FILES[$field]['tmp_name'], $_FILES[$field]['name']);
            }
        }

        // Send mail
        $mail->send();
        echo "Form submitted successfully!";
    } catch (Exception $e) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    }
}
?>
