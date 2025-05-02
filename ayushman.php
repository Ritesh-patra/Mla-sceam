<?php
// Import PHPMailer classes manually (without using Composer)
require 'mailer/Exception.php';
require 'mailer/PHPMailer.php';
require 'mailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Initialize error array
    $errors = [];
    
    // Validate required fields
    $required_fields = ['full-name', 'father-name', 'email', 'mobile', 'ward', 'pin-code', 'address', 'complaint'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('-', ' ', $field)) . " is required";
        }
    }
    
    // Validate email format
    if (!empty($_POST['email']) && !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate mobile number (10 digits)
    if (!empty($_POST['mobile']) && !preg_match("/^[0-9]{10}$/", $_POST['mobile'])) {
        $errors[] = "Mobile number must be 10 digits";
    }
    
    // Validate PIN code (6 digits)
    if (!empty($_POST['pin-code']) && !preg_match("/^[0-9]{6}$/", $_POST['pin-code'])) {
        $errors[] = "PIN code must be 6 digits";
    }
    
    // File upload validation
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    
    // Function to validate file
    function validateFile($file, $allowed_extensions, $max_file_size) {
        if ($file['error'] == 4) { // No file uploaded
            return "File is required";
        }
        
        if ($file['error'] != 0) {
            return "Error uploading file";
        }
        
        if ($file['size'] > $max_file_size) {
            return "File size exceeds 2MB limit";
        }
        
        $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($file_extension, $allowed_extensions)) {
            return "Invalid file format. Only " . implode(', ', $allowed_extensions) . " files are allowed";
        }
        
        return null;
    }
    
    // Validate Aadhar Card file
    $aadhar_error = validateFile($_FILES['aadhar-card'], $allowed_extensions, $max_file_size);
    if ($aadhar_error) {
        $errors[] = "Aadhar Card: " . $aadhar_error;
    }
    
    // If there are no errors, process the form
    if (empty($errors)) {
        // Create upload directory if it doesn't exist
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Upload Aadhar Card
        $aadhar_filename = $upload_dir . time() . '_' . basename($_FILES['aadhar-card']['name']);
        move_uploaded_file($_FILES['aadhar-card']['tmp_name'], $aadhar_filename);
        
      
        
        // Prepare email content
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kanilkumarbjp@gmail.com'; // As requested
            $mail->Password = 'mhha amzy hwsi rdxy'; // As requested
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('kanilkumarbjp@gmail.com', 'Ayushman Bharat System');
            $mail->addAddress('kanilkumarbjp@gmail.com'); // Where to send the form data
            
            // Add reply-to with the user's email
            $mail->addReplyTo($_POST['email'], $_POST['full-name']);
            
            // Attachments
            $mail->addAttachment($aadhar_filename);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Ayushman Bharat Application: ' . $reference_number;
            
            // Build email body
            $body = '<h2>Ayushman Bharat Application Details</h2>';
            $body .= '<p><strong>Reference Number:</strong> ' . $reference_number . '</p>';
            $body .= '<p><strong>Full Name:</strong> ' . $_POST['full-name'] . '</p>';
            $body .= '<p><strong>Father Name:</strong> ' . $_POST['father-name'] . '</p>';
            $body .= '<p><strong>Email:</strong> ' . $_POST['email'] . '</p>';
            $body .= '<p><strong>Mobile:</strong> ' . $_POST['mobile'] . '</p>';
            $body .= '<p><strong>Gender:</strong> ' . $_POST['gender'] . '</p>';
            $body .= '<p><strong>Ward:</strong> ' . $_POST['ward'] . '</p>';
            $body .= '<p><strong>Pin Code:</strong> ' . $_POST['pin-code'] . '</p>';
            $body .= '<p><strong>Address:</strong> ' . $_POST['address'] . '</p>';
            $body .= '<p><strong>Complaint Description:</strong> ' . nl2br($_POST['complaint']) . '</p>';
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            $mail->send();
            
            // Redirect to success page or show success message
            echo '<div style="max-width: 800px; margin: 50px auto; padding: 20px; background-color: #f0f8ff; border-radius: 10px; text-align: center;">';
            echo '<h2 style="color: #4052b5;">Application Submitted Successfully!</h2>';
            echo '<p>Your Ayushman Bharat application has been submitted successfully.</p>';
            echo '<p>Your reference number is: <strong>' . $reference_number . '</strong></p>';
            echo '<p>Please keep this reference number for future correspondence.</p>';
            echo '<p>We will contact you soon at your provided email or mobile number.</p>';
            echo '<a href="index.html" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4052b5; color: white; text-decoration: none; border-radius: 5px;">Back to Home</a>';
            echo '</div>';
            
           
            
            exit;
            
        } catch (Exception $e) {
            $errors[] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    
    // If there are errors, display them
    if (!empty($errors)) {
        echo '<div style="max-width: 800px; margin: 50px auto; padding: 20px; background-color: #fff0f0; border-radius: 10px;">';
        echo '<h2 style="color: #e53e3e;">Error Submitting Form</h2>';
        echo '<ul style="color: #e53e3e;">';
        foreach ($errors as $error) {
            echo '<li>' . $error . '</li>';
        }
        echo '</ul>';
        echo '<a href="javascript:history.back()" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4052b5; color: white; text-decoration: none; border-radius: 5px;">Go Back</a>';
        echo '</div>';
        exit;
    }
}
else {
    // If the form was not submitted, redirect to the form page
    header("Location: index.html");
    exit;
}
?>