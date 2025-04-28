<?php

// Include PHPMailer files
require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Set headers for AJAX response
header('Content-Type: application/json');

// Function to sanitize input data
function sanitize_input($data) {
    return htmlspecialchars(stripslashes(trim($data)));
}

// Initialize response array
$response = [
    'success' => false,
    'message' => 'An error occurred while processing your request.'
];

// Function to handle file upload
function handleFileUpload($fileField, $allowedExtensions, $maxFileSize) {
    if (!isset($_FILES[$fileField]) || $_FILES[$fileField]['error'] != UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => "Error uploading $fileField."];
    }

    $file = $_FILES[$fileField];
    $fileName = $file['name'];
    $fileTmpName = $file['tmp_name'];
    $fileSize = $file['size'];
    $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if (!in_array($fileExt, $allowedExtensions)) {
        return ['success' => false, 'message' => "Invalid file type for $fileField."];
    }

    if ($fileSize > $maxFileSize) {
        return ['success' => false, 'message' => "$fileField exceeds 2MB limit."];
    }

    $uploadDir = 'uploads/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $newFileName = uniqid() . '.' . $fileExt;
    $uploadPath = $uploadDir . $newFileName;

    if (move_uploaded_file($fileTmpName, $uploadPath)) {
        return ['success' => true, 'path' => $uploadPath, 'name' => $fileName];
    } else {
        return ['success' => false, 'message' => "Failed to move $fileField."];
    }
}

// Process form
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Collect input
    $letter_no = sanitize_input($_POST['letter_no'] ?? '');
    $full_name = sanitize_input($_POST['full_name'] ?? '');
    $father_name = sanitize_input($_POST['father_name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $mobile = sanitize_input($_POST['mobile'] ?? '');
    $gender = sanitize_input($_POST['gender'] ?? '');
    $district = sanitize_input($_POST['district'] ?? '');
    $zone = sanitize_input($_POST['zone'] ?? '');
    $gram_panchayat = sanitize_input($_POST['gram_panchayat'] ?? '');
    $village = sanitize_input($_POST['village'] ?? '');
    $pin_code = sanitize_input($_POST['pin_code'] ?? '');
    $address = sanitize_input($_POST['address'] ?? '');

    // Validate mandatory fields
    if (empty($full_name) || empty($father_name) || empty($email) || empty($mobile) ||
        empty($zone) || empty($gram_panchayat) || empty($pin_code) || empty($address)) {
        $response['message'] = 'Please fill in all required fields.';
        echo json_encode($response);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $response['message'] = 'Please enter a valid email address.';
        echo json_encode($response);
        exit;
    }

    // Upload files
    $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB

    $uploadedFiles = [];

    $aadharUpload = handleFileUpload('aadhar_card', $allowedExtensions, $maxFileSize);
    if (!$aadharUpload['success']) {
        $response['message'] = $aadharUpload['message'];
        echo json_encode($response);
        exit;
    }
    $uploadedFiles['aadhar_card'] = $aadharUpload;

    $photoUpload = handleFileUpload('passport_photo', $allowedExtensions, $maxFileSize);
    if (!$photoUpload['success']) {
        $response['message'] = $photoUpload['message'];
        echo json_encode($response);
        exit;
    }
    $uploadedFiles['passport_photo'] = $photoUpload;

    // Email body
    $emailBody = "
    <html>
    <head><style>
    body { font-family: Arial, sans-serif; }
    table { border-collapse: collapse; width: 100%; }
    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background-color: #f2f2f2; }
    </style></head>
    <body>
    <h2>New Bhumihina Application</h2>
    <table>
        <tr><th>Letter No.</th><td>$letter_no</td></tr>
        <tr><th>Full Name</th><td>$full_name</td></tr>
        <tr><th>Father Name</th><td>$father_name</td></tr>
        <tr><th>Email</th><td>$email</td></tr>
        <tr><th>Mobile</th><td>$mobile</td></tr>
        <tr><th>Gender</th><td>$gender</td></tr>
        <tr><th>District</th><td>$district</td></tr>
        <tr><th>Ward</th><td>$zone</td></tr>
        <tr><th>Gram Panchayat</th><td>$gram_panchayat</td></tr>
        <tr><th>Village/Locality</th><td>$village</td></tr>
        <tr><th>Pin Code</th><td>$pin_code</td></tr>
        <tr><th>Address</th><td>$address</td></tr>
    </table>
    <p>Attached documents for verification.</p>
    </body>
    </html>";

    // Send Email
    try {
        $mail = new PHPMailer(true);

        // SMTP server config
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'patrasagarika654@gmail.com';
        $mail->Password   = 'dqnk duhw jwxz uydo'; // App password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender & recipient
        $mail->setFrom('patrasagarika654@gmail.com', 'Bhumihina Form');
        $mail->addAddress('patrasagarika654@gmail.com');
        $mail->addReplyTo($email, $full_name);

        // Attach both files
        if (file_exists($uploadedFiles['aadhar_card']['path'])) {
            $mail->addAttachment($uploadedFiles['aadhar_card']['path'], $uploadedFiles['aadhar_card']['name']);
        }
        if (file_exists($uploadedFiles['passport_photo']['path'])) {
            $mail->addAttachment($uploadedFiles['passport_photo']['path'], $uploadedFiles['passport_photo']['name']);
        }

        // Content
        $mail->isHTML(true);
        $mail->Subject = "New Bhumihina Application - $letter_no";
        $mail->Body    = $emailBody;
        $mail->AltBody = strip_tags($emailBody);

        $mail->send();

        $response['success'] = true;
        $response['message'] = 'Your application has been submitted successfully. We will contact you soon.';

    } catch (Exception $e) {
        $response['message'] = "Mailer Error: {$mail->ErrorInfo}";
    }
}

// Return JSON response
echo json_encode($response);

?>
