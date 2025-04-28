<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

// Initialize response array
$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $requiredFields = [
            'category', 'full_name', 'father_name', 'email', 
            'mobile', 'address', 'pin_code', 'letter_no'
        ];
        
        foreach ($requiredFields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("Please fill all required fields");
            }
        }

        // Validate email
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address");
        }

        // Validate mobile number (Indian format)
        if (!preg_match('/^[6-9]\d{9}$/', $_POST['mobile'])) {
            throw new Exception("Invalid mobile number");
        }

        // Validate PIN code (Indian format)
        if (!preg_match('/^\d{6}$/', $_POST['pin_code'])) {
            throw new Exception("Invalid PIN code");
        }

        // Initialize PHPMailer
        $mail = new PHPMailer(true);

        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'patrasagarika654@gmail.com';
        $mail->Password = 'dqnk duhw jwxz uydo';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';

        // Recipients
        $mail->setFrom('patrasagarika654@gmail.com', 'Grievance Redressal System');
        $mail->addAddress('patrasagarika654@gmail.com', 'Admin');
        $mail->addReplyTo($_POST['email'], $_POST['full_name']);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'New Grievance Submission - ' . $_POST['category'];

        // Build email body with your original fields
        $emailBody = '<h2 style="color:#4052b5;">Grievance Details</h2>';
        $emailBody .= '<table style="width:100%; border-collapse:collapse;">';
        
        // Add form fields to email (keeping your original field names)
        $fields = [
            'Letter No./Date' => $_POST['letter_no'],
            'Category' => $_POST['category'],
            'Full Name' => htmlspecialchars($_POST['full_name']),
            "Father's Name" => htmlspecialchars($_POST['father_name']),
            'Email' => htmlspecialchars($_POST['email']),
            'Mobile' => htmlspecialchars($_POST['mobile']),
            'Gender' => $_POST['gender'] ?? 'Not specified',
            'District' => $_POST['district'] ?? 'Not specified',
            'Ward' => $_POST['ward'] ?? 'Not specified',
            'Village/Locality' => htmlspecialchars($_POST['village'] ?? 'Not specified'),
            'PIN Code' => htmlspecialchars($_POST['pin_code']),
            'Address' => htmlspecialchars($_POST['address'])
        ];
        
        foreach ($fields as $label => $value) {
            $emailBody .= '<tr><td style="padding:8px;border:1px solid #ddd;width:30%;"><strong>' . $label . '</strong></td>';
            $emailBody .= '<td style="padding:8px;border:1px solid #ddd;">' . $value . '</td></tr>';
        }
        
        $emailBody .= '</table>';
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace('<br />', "\n", $emailBody));

        // Handle file attachments (using your original field names)
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        // Process file uploads (using your original field names)
        $fileFields = [
            'aadhar_card', 'bank_passbook', 'income_certificate', 
            'signature', 'passport_photo', 'voter_card'
        ];
        
        foreach ($fileFields as $field) {
            if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
                $fileExt = strtolower(pathinfo($_FILES[$field]['name'], PATHINFO_EXTENSION));
                
                if (!in_array($fileExt, $allowedExtensions)) {
                    throw new Exception("Invalid file type for " . str_replace('_', ' ', $field) . 
                        ". Allowed: " . implode(', ', $allowedExtensions));
                }
                
                if ($_FILES[$field]['size'] > $maxFileSize) {
                    throw new Exception(ucfirst(str_replace('_', ' ', $field)) . 
                        " file is too large. Max 2MB allowed.");
                }
                
                $mail->addAttachment(
                    $_FILES[$field]['tmp_name'],
                    ucfirst(str_replace('_', ' ', $field)) . '_' . $_POST['full_name'] . '.' . $fileExt
                );
            } elseif (in_array($field, ['aadhar_card', 'bank_passbook', 'income_certificate', 'signature', 'passport_photo'])) {
                // These are required files in your original code
                throw new Exception(ucfirst(str_replace('_', ' ', $field)) . " is required");
            }
        }

        // Send email
        $mail->send();
        
        // Success response
        $response['success'] = true;
        $response['message'] = 'Your grievance has been submitted successfully. We will contact you soon.';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// If not a POST request, redirect to form
header('Location: index.html');
exit;
?>