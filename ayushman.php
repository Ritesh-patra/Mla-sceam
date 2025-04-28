<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Initialize response array
    $response = ['success' => false, 'message' => ''];
    
    try {
        // Validate required fields
        $requiredFields = [
            'category', 'full-name', 'father-name', 'email', 
            'mobile', 'address', 'pin-code', 'complaint'
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
        if (!preg_match('/^\d{6}$/', $_POST['pin-code'])) {
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
        $mail->setFrom('patrasagarika654@gmail.com', 'Ayushman Cards Redressal System');
        $mail->addAddress('patrasagarika654@gmail.com', 'Admin');
        $mail->addReplyTo($_POST['email'], $_POST['full-name']);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'New Ayushman Cars Submission - ' . $_POST['category'];

        // Build email body
        $emailBody = '<h2 style="color:#4052b5;">Ayushman Cards Details</h2>';
        $emailBody .= '<table style="width:100%; border-collapse:collapse;">';
        
        // Add form fields to email
        $fields = [
            'Letter No./Date' => $_POST['letter-no'] ?? 'N/A',
            'Category' => $_POST['category'],
            'Full Name' => htmlspecialchars($_POST['full-name']),
            "Father's Name" => htmlspecialchars($_POST['father-name']),
            'Email' => htmlspecialchars($_POST['email']),
            'Mobile' => htmlspecialchars($_POST['mobile']),
            'Gender' => $_POST['gender'] ?? 'Not specified',
            'District' => $_POST['district'] ?? 'GANJAM',
            'Ward' => $_POST['ward'] ?? 'Not specified',
            'Village/Locality' => htmlspecialchars($_POST['village'] ?? 'Not specified'),
            'PIN Code' => htmlspecialchars($_POST['pin-code']),
            'Address' => htmlspecialchars($_POST['address']),
            'Dealer Name' => htmlspecialchars($_POST['dealer-name'] ?? 'Not specified'),
            'Dealer Code' => htmlspecialchars($_POST['dealer-code'] ?? 'Not specified'),
            'Complaint' => nl2br(htmlspecialchars($_POST['complaint']))
        ];
        
        foreach ($fields as $label => $value) {
            $emailBody .= '<tr><td style="padding:8px;border:1px solid #ddd;width:30%;"><strong>' . $label . '</strong></td>';
            $emailBody .= '<td style="padding:8px;border:1px solid #ddd;">' . $value . '</td></tr>';
        }
        
        // Add member details if applicable
        if ($_POST['category'] === 'add-member' && isset($_POST['member-name'])) {
            $emailBody .= '<tr><td colspan="2" style="padding:8px;border:1px solid #ddd;background:#f5f5f5;">';
            $emailBody .= '<h3 style="margin:0;">Additional Members</h3></td></tr>';
            
            foreach ($_POST['member-name'] as $index => $name) {
                $emailBody .= '<tr><td colspan="2" style="padding:8px;border:1px solid #ddd;background:#f9f9f9;">';
                $emailBody .= '<strong>Member ' . ($index + 1) . '</strong></td></tr>';
                
                $memberFields = [
                    'Name' => htmlspecialchars($name),
                    'Relation' => htmlspecialchars($_POST['member-relation'][$index]),
                    'Age' => htmlspecialchars($_POST['member-age'][$index]),
                    'Gender' => htmlspecialchars($_POST['member-gender'][$index]),
                    'Aadhar Number' => htmlspecialchars($_POST['member-aadhar'][$index] ?? 'Not provided')
                ];
                
                foreach ($memberFields as $mLabel => $mValue) {
                    $emailBody .= '<tr><td style="padding:8px;border:1px solid #ddd;padding-left:30px;">' . $mLabel . '</td>';
                    $emailBody .= '<td style="padding:8px;border:1px solid #ddd;">' . $mValue . '</td></tr>';
                }
            }
        }
        
        $emailBody .= '</table>';
        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace('<br />', "\n", $emailBody));

        // Handle file attachments
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'png'];
        $maxFileSize = 2 * 1024 * 1024; // 2MB
        
        // Process Aadhar Card upload
        if (isset($_FILES['aadhar-card']) && $_FILES['aadhar-card']['error'] === UPLOAD_ERR_OK) {
            $fileExt = strtolower(pathinfo($_FILES['aadhar-card']['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file type for Aadhar Card. Only PDF, DOC, DOCX, XLS, XLSX allowed.");
            }
            if ($_FILES['aadhar-card']['size'] > $maxFileSize) {
                throw new Exception("Aadhar Card file is too large. Max 2MB allowed.");
            }
            $mail->addAttachment(
                $_FILES['aadhar-card']['tmp_name'],
                'Aadhar_' . $_POST['full-name'] . '.' . $fileExt
            );
        }
        
        // Process Bank Passbook upload
        if (isset($_FILES['bank-passbook']) && $_FILES['bank-passbook']['error'] === UPLOAD_ERR_OK) {
            $fileExt = strtolower(pathinfo($_FILES['bank-passbook']['name'], PATHINFO_EXTENSION));
            if (!in_array($fileExt, $allowedExtensions)) {
                throw new Exception("Invalid file type for Bank Passbook. Only PDF, DOC, DOCX, XLS, XLSX allowed.");
            }
            if ($_FILES['bank-passbook']['size'] > $maxFileSize) {
                throw new Exception("Bank Passbook file is too large. Max 2MB allowed.");
            }
            $mail->addAttachment(
                $_FILES['bank-passbook']['tmp_name'],
                'BankPassbook_' . $_POST['full-name'] . '.' . $fileExt
            );
        }

        // Send email
        $mail->send();
        
        // Success response
        $response['success'] = true;
        $response['message'] = 'Your Ayushman Cards has been submitted successfully. We will contact you soon.';
        
    } catch (Exception $e) {
        $response['message'] = 'Error: ' . $e->getMessage();
    }
    
    // Display response message
    echo "<script>
        alert('".addslashes($response['message'])."');
        window.location.href = 'success.php';
    </script>";
    exit;
}

// If not a POST request, redirect to form
header('Location: success.php');
exit;
?>