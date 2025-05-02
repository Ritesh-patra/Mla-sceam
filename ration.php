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
    $required_fields = ['full-name', 'father-name', 'mobile', 'ward', 'pin-code', 'address', 'complaint'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = ucfirst(str_replace('-', ' ', $field)) . " is required";
        }
    }
    
    // Validate email format if provided
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
    
    // If there are no errors, process the form
    if (empty($errors)) {
        // Generate a unique reference number

        
        // Prepare email content
        $mail = new PHPMailer(true);
        
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'kanilkumarbjp@gmail.com';
            $mail->Password = 'mhha amzy hwsi rdxy';
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;
            
            // Recipients
            $mail->setFrom('kanilkumarbjp@gmail.com', 'Ration Card System');
            $mail->addAddress('kanilkumarbjp@gmail.com');
            
            // Add reply-to with the user's email if provided
            if (!empty($_POST['email'])) {
                $mail->addReplyTo($_POST['email'], $_POST['full-name']);
            }
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Ration Card Application: ' . $reference_number;
            
            // Build email body
            $body = '<h2>Ration Card Application Details</h2>';
            $body .= '<p><strong>Reference Number:</strong> ' . $reference_number . '</p>';
            $body .= '<p><strong>Full Name:</strong> ' . htmlspecialchars($_POST['full-name']) . '</p>';
            $body .= '<p><strong>Father Name:</strong> ' . htmlspecialchars($_POST['father-name']) . '</p>';
            
            if (!empty($_POST['email'])) {
                $body .= '<p><strong>Email:</strong> ' . htmlspecialchars($_POST['email']) . '</p>';
            } else {
                $body .= '<p><strong>Email:</strong> Not provided</p>';
            }
            
            $body .= '<p><strong>Mobile:</strong> ' . htmlspecialchars($_POST['mobile']) . '</p>';
            $body .= '<p><strong>Gender:</strong> ' . htmlspecialchars($_POST['gender'] ?? 'Not specified') . '</p>';
            $body .= '<p><strong>Ward:</strong> ' . htmlspecialchars($_POST['ward']) . '</p>';
            $body .= '<p><strong>Pin Code:</strong> ' . htmlspecialchars($_POST['pin-code']) . '</p>';
            $body .= '<p><strong>Address:</strong> ' . htmlspecialchars($_POST['address']) . '</p>';
            $body .= '<p><strong>Complaint Description:</strong> ' . nl2br(htmlspecialchars($_POST['complaint'])) . '</p>';
            
            $mail->Body = $body;
            $mail->AltBody = strip_tags($body);
            
            // Send email
            $mail->send();
            
            // Display success message
            echo '<div style="max-width: 800px; margin: 50px auto; padding: 20px; background-color: #f0f8ff; border-radius: 10px; text-align: center;">';
            echo '<h2 style="color: #4052b5;">Application Submitted Successfully!</h2>';
            echo '<p>Your Ration Card application has been submitted successfully.</p>';
            echo '<p>Your reference number is: <strong>' . htmlspecialchars($reference_number) . '</strong></p>';
            echo '<p>Please keep this reference number for future correspondence.</p>';
            echo '<p>We will contact you soon at your provided mobile number.</p>';
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
            echo '<li>' . htmlspecialchars($error) . '</li>';
        }
        echo '</ul>';
        echo '<a href="javascript:history.back()" style="display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4052b5; color: white; text-decoration: none; border-radius: 5px;">Go Back</a>';
        echo '</div>';
        exit;
    }
} else {
    // If the form was not submitted, redirect to the form page
    header("Location: index.html");
    exit;
}
?>