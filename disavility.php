<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

// Check if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize response
    $response = ['success' => false, 'message' => ''];

    try {
        // Validate required fields
        $requiredFields = [
            'fullName', 'fatherName', 'email', 'mobile', 'address', 'pinCode'
        ];
        
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
        if (!preg_match('/^\d{6}$/', $_POST['pinCode'])) {
            throw new Exception("Invalid PIN code.");
        }

        // Setup PHPMailer
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kanilkumarbjp@gmail.com'; // Your Email
        $mail->Password   = 'mhha amzy hwsi rdxy';         // Your App Password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';

        // Set sender and receiver
        $mail->setFrom('kanilkumarbjp@gmail.com', 'Disability Certificate Form');
        $mail->addAddress('kanilkumarbjp@gmail.com', 'Admin');
        $mail->addReplyTo($_POST['email'], $_POST['fullName']);

        // Email subject
        $letterNo = htmlspecialchars($_POST['letterNo'] ?? 'disavility application');
        $mail->isHTML(true);
        $mail->Subject = "New Disability Certificate Application ";

        // Email body
        $emailBody = '<h2 style="color:#4052b5;">Applicant Details</h2>';
        $emailBody .= '<table style="width:100%; border-collapse:collapse;">';

        $fields = [
      
            'Full Name' => htmlspecialchars(string: $_POST['fullName']),
            "Father's Name" => htmlspecialchars($_POST['fatherName']),
            'Email' => htmlspecialchars($_POST['email']),
            'Mobile' => htmlspecialchars($_POST['mobile']),
            'Gender' => htmlspecialchars($_POST['gender'] ?? 'Not specified'),
            'District' => htmlspecialchars($_POST['district'] ?? 'GANJAM'),
            'Ward' => htmlspecialchars($_POST['ward'] ?? 'Not specified'),
            'Village/Locality' => htmlspecialchars($_POST['village'] ?? 'Not specified'),
            'PIN Code' => htmlspecialchars($_POST['pinCode']),
            'Address' => htmlspecialchars($_POST['address']),
        ];

        foreach ($fields as $label => $value) {
            $emailBody .= '<tr><td style="padding:8px;border:1px solid #ddd;width:30%;"><strong>' . $label . '</strong></td>';
            $emailBody .= '<td style="padding:8px;border:1px solid #ddd;">' . $value . '</td></tr>';
        }

        $emailBody .= '</table>';
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace('<br />', "\n", $emailBody));

        // Attachments (Aadhar Card and Passport Photo)
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB

        if (isset($_FILES['aadharCard']) && $_FILES['aadharCard']['error'] === UPLOAD_ERR_OK) {
            $fileExt = strtolower(pathinfo($_FILES['aadharCard']['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file type for Aadhar Card. Only PDF, DOC, DOCX, XLS, XLSX allowed.");
            }
            if ($_FILES['aadharCard']['size'] > $maxFileSize) {
                throw new Exception("Aadhar Card file is too large. Max 2MB allowed.");
            }
            $mail->addAttachment($_FILES['aadharCard']['tmp_name'], 'AadharCard_' . $_POST['fullName'] . '.' . $fileExt);
        }

        if (isset($_FILES['passportPhoto']) && $_FILES['passportPhoto']['error'] === UPLOAD_ERR_OK) {
            $fileExt = strtolower(pathinfo($_FILES['passportPhoto']['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file type for Passport Photo. Only PDF, DOC, DOCX, XLS, XLSX allowed.");
            }
            if ($_FILES['passportPhoto']['size'] > $maxFileSize) {
                throw new Exception("Passport Photo file is too large. Max 2MB allowed.");
            }
            $mail->addAttachment($_FILES['passportPhoto']['tmp_name'], 'PassportPhoto_' . $_POST['fullName'] . '.' . $fileExt);
        }

        // Send Email
        $mail->send();

        $response['success'] = true;
        $response['message'] = 'Your application has been submitted successfully.';

    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }

    // Alert and redirect
    echo "<script>
        alert('".addslashes($response['message'])."');
        window.location.href = 'index.html';
    </script>";
    exit;
}

// If not POST, Redirect
header('Location: index.html');
exit;
?>
