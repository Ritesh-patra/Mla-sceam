<?php
// Set your email credentials and settings
$to_email = "patrasagarika654@gmail.com";
$subject_prefix = "Application Form Submission - ";

// SMTP Configuration (using Gmail)
$smtp_host = "smtp.gmail.com";
$smtp_port = 587;
$smtp_username = "patrasagarika654@gmail.com";
$smtp_password = "yder qkfe hbng lfcj"; // Your app password
$smtp_from_email = "patrasagarika654@gmail.com";
$smtp_from_name = "RAJARANI Group";

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate and sanitize inputs
    $fullName = sanitizeInput($_POST['fullName']);
    $fatherName = sanitizeInput($_POST['fatherName']);
    $mobile = sanitizeInput($_POST['mobile']);
    $email = sanitizeInput($_POST['email']);
    $address = sanitizeInput($_POST['address']);
    $district = sanitizeInput($_POST['district']);
    $category = sanitizeInput($_POST['category']);
    $schemeType = sanitizeInput($_POST['schemeType']);
    $projectCost = sanitizeInput($_POST['projectCost']);
    $projectDescription = sanitizeInput($_POST['projectDescription']);
    $landArea = sanitizeInput($_POST['landArea']);
    $waterSource = sanitizeInput($_POST['waterSource']);
    
    // Basic validation
    $errors = [];
    if (empty($fullName)) $errors[] = "Full Name is required";
    if (empty($fatherName)) $errors[] = "Father's/Spouse's Name is required";
    if (empty($mobile)) $errors[] = "Mobile Number is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid Email format";
    if (empty($_POST['declaration'])) $errors[] = "You must accept the declaration";
    
    if (count($errors) > 0) {
        // Display errors
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>";
        echo "<h3>Error submitting form:</h3>";
        echo "<ul>";
        foreach ($errors as $error) {
            echo "<li>$error</li>";
        }
        echo "</ul>";
        echo "<p>Please go back and correct these errors.</p>";
        echo "</div>";
        exit;
    }
    
    // Prepare email message
    $subject = $subject_prefix . $fullName;
    
    $message = "
    <html>
    <head>
        <title>$subject</title>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; }
            .header { background-color: #f8f9fa; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .section { margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 20px; }
            .section-title { font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
            .field { margin-bottom: 8px; }
            .field-label { font-weight: bold; display: inline-block; width: 200px; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>New Application Form Submission</h2>
        </div>
        
        <div class='content'>
            <div class='section'>
                <div class='section-title'>Personal Information</div>
                <div class='field'><span class='field-label'>Full Name:</span> $fullName</div>
                <div class='field'><span class='field-label'>Father's/Spouse's Name:</span> $fatherName</div>
                <div class='field'><span class='field-label'>Mobile Number:</span> $mobile</div>
                <div class='field'><span class='field-label'>Email Address:</span> $email</div>
                <div class='field'><span class='field-label'>Complete Address:</span> $address</div>
                <div class='field'><span class='field-label'>District:</span> $district</div>
                <div class='field'><span class='field-label'>Category:</span> $category</div>
            </div>
            
            <div class='section'>
                <div class='section-title'>Project Details</div>
                <div class='field'><span class='field-label'>Scheme Type:</span> $schemeType</div>
                <div class='field'><span class='field-label'>Estimated Project Cost:</span> ₹$projectCost</div>
                <div class='field'><span class='field-label'>Project Description:</span> $projectDescription</div>
                <div class='field'><span class='field-label'>Land Area:</span> $landArea</div>
                <div class='field'><span class='field-label'>Water Source:</span> $waterSource</div>
            </div>
            
            <div class='section'>
                <div class='section-title'>Uploaded Documents</div>
                <div class='field'>The applicant has uploaded all required documents.</div>
            </div>
        </div>
    </body>
    </html>
    ";
    
    // Handle file uploads (save to server temporarily)
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    $file_attachments = [];
    $file_fields = ['aadhaar', 'pan', 'bankDetails', 'businessReg', 'projectReport', 'landDocuments', 'partnershipDeed'];
    
    foreach ($file_fields as $field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == UPLOAD_ERR_OK) {
            $file_name = basename($_FILES[$field]['name']);
            $file_path = $upload_dir . uniqid() . '_' . $file_name;
            
            if (move_uploaded_file($_FILES[$field]['tmp_name'], $file_path)) {
                $file_attachments[] = $file_path;
            }
        }
    }
    
    // Send email with PHPMailer (native PHP mail() function is not reliable)
    require 'PHPMailer/PHPMailer.php';
    require 'PHPMailer/SMTP.php';
    require 'PHPMailer/Exception.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = $smtp_host;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtp_username;
        $mail->Password   = $smtp_password;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = $smtp_port;
        
        // Recipients
        $mail->setFrom($smtp_from_email, $smtp_from_name);
        $mail->addAddress($to_email);
        $mail->addReplyTo($email, $fullName);
        
        // Attachments
        foreach ($file_attachments as $file) {
            $mail->addAttachment($file);
        }
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $message;
        $mail->AltBody = strip_tags($message);
        
        $mail->send();
        
        // Clean up uploaded files
        foreach ($file_attachments as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        
        // Success message
        echo "<div style='text-align: center; padding: 50px;'>
                <h2 style='color: #4CAF50;'>Thank You!</h2>
                <p>Your application has been submitted successfully.</p>
                <p>We will contact you shortly regarding your application.</p>
                <a href='index.html' style='display: inline-block; margin-top: 20px; padding: 10px 20px; background-color: #4CAF50; color: white; text-decoration: none; border-radius: 5px;'>Return to Home</a>
              </div>";
    } catch (Exception $e) {
        echo "<div style='color: red; padding: 20px; border: 1px solid red; margin: 20px;'>
                <h3>Error sending email:</h3>
                <p>{$mail->ErrorInfo}</p>
                <p>Please try again later or contact support.</p>
              </div>";
    }
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Form Submission</title>
</head>
<body>
    <!-- Your HTML form would be here -->
</body>
</html>