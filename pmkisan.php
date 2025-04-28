<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = ['success' => false, 'message' => ''];

    try {
        // Validate required fields
        $requiredFields = ['full_name', 'father_name', 'email', 'mobile', 'district', 'ward', 'pin_code', 'address'];
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill all required fields.");
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address.");
        }

        // Validate mobile number
        if (!preg_match('/^[6-9]\d{9}$/', $_POST['mobile'])) {
            throw new Exception("Invalid mobile number.");
        }

        // Validate PIN code
        if (!preg_match('/^\d{6}$/', $_POST['pin_code'])) {
            throw new Exception("Invalid PIN code.");
        }

        // Validate file uploads
        $requiredFiles = ['aadhar_card', 'passport_photo', 'bank_passbook', 'chasa_jami_pata'];
        foreach ($requiredFiles as $file) {
            if (!isset($_FILES[$file]) || $_FILES[$file]['error'] != UPLOAD_ERR_OK) {
                throw new Exception("Please upload all required documents.");
            }

            // Check file size and type
            $fileExt = strtolower(pathinfo($_FILES[$file]['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
            $maxFileSize = 2 * 1024 * 1024; // 2MB

            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file type for $file. Only PDF, DOC, DOCX, XLS, XLSX allowed.");
            }

            if ($_FILES[$file]['size'] > $maxFileSize) {
                throw new Exception("$file file is too large. Max 2MB allowed.");
            }
        }

        // Setup PHPMailer
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'patrasagarika654@gmail.com'; // Your Email
        $mail->Password   = 'dqnk duhw jwxz uydo';   // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Set sender and receiver
        $mail->setFrom('patrasagarika654@gmail.com', 'CM Kisan Yojana');
        $mail->addAddress('patrasagarika654@gmail.com', 'Admin'); // Admin email
        $mail->addReplyTo($_POST['email'], $_POST['full_name']);

        // Email subject
        $mail->isHTML(true);
        $mail->Subject = "New CM Kisan Yojana Application - " . htmlspecialchars($_POST['letter_no']);

        // Email body
        $emailBody = '<h2 style="color:#4052b5;">Applicant Details</h2>';
        $emailBody .= '<table style="width:100%; border-collapse:collapse;">';
        $fields = [
            'Letter No./Date' => htmlspecialchars($_POST['letter_no']),
            'Full Name' => htmlspecialchars($_POST['full_name']),
            'Father Name' => htmlspecialchars($_POST['father_name']),
            'Email' => htmlspecialchars($_POST['email']),
            'Mobile' => htmlspecialchars($_POST['mobile']),
            'Gender' => htmlspecialchars($_POST['gender'] ?? 'Not specified'),
            'District' => htmlspecialchars($_POST['district']),
            'Ward' => htmlspecialchars($_POST['ward']),
            'Village/Locality' => htmlspecialchars($_POST['village'] ?? 'Not specified'),
            'PIN Code' => htmlspecialchars($_POST['pin_code']),
            'Address' => htmlspecialchars($_POST['address']),
        ];

        foreach ($fields as $label => $value) {
            $emailBody .= '<tr><td style="padding:8px;border:1px solid #ddd;width:30%;"><strong>' . $label . '</strong></td>';
            $emailBody .= '<td style="padding:8px;border:1px solid #ddd;">' . $value . '</td></tr>';
        }

        $emailBody .= '</table>';
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace('<br />', "\n", $emailBody));

        // Attach files
        $attachments = [
            'aadhar_card' => 'Aadhar Card',
            'passport_photo' => 'Passport Photo',
            'bank_passbook' => 'Bank Passbook',
            'chasa_jami_pata' => 'Chasa Jami Pata'
        ];

        foreach ($attachments as $inputName => $attachmentName) {
            $fileExt = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
            $mail->addAttachment($_FILES[$inputName]['tmp_name'], $attachmentName . '_' . $_POST['full_name'] . '.' . $fileExt);
        }

        // Send Email
        $mail->send();

        $response['success'] = true;
        $response['message'] = 'Your application has been submitted successfully.';

    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }

    // Display response
    echo "<script>
        alert('".addslashes($response['message'])."');
        window.location.href = 'index.html';
    </script>";
    exit;
}

// If not POST, redirect to form
header('Location: index.html');
exit;
?>
