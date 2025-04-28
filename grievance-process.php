<?php
// Import PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoloader
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $letter_no = $_POST['letter_no'] ?? '';
    $full_name = $_POST['full_name'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $mobile = $_POST['mobile'] ?? '';
    $gender = $_POST['gender'] ?? '';
    $district = $_POST['district'] ?? '';
    $ward = $_POST['wa$ward'] ?? '';
    $village = $_POST['village'] ?? '';
    $pin_code = $_POST['pin_code'] ?? '';
    $address = $_POST['address'] ?? '';
    
    // Validate required fields
    $errors = [];
    
    if (empty($full_name)) $errors[] = "Full name is required";
    if (empty($father_name)) $errors[] = "Father name is required";
    if (empty($email)) $errors[] = "Email is required";
    if (empty($mobile)) $errors[] = "Mobile is required";
    if (empty($district)) $errors[] = "District is required";
    if (empty($ward)) $errors[] = "Ward is required";
    if (empty($pin_code)) $errors[] = "Pin code is required";
    if (empty($address)) $errors[] = "Address is required";
    
    // Check file uploads
    $required_files = ['aadhar_card', 'bank_passbook', 'income_certificate', 'ghara_pata', 'passport_photo', 'house_photo'];
    $allowed_extensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $max_file_size = 2 * 1024 * 1024; // 2MB
    
    $file_uploads = [];
    
    foreach ($required_files as $file) {
        if (!isset($_FILES[$file]) || $_FILES[$file]['error'] != 0) {
            $errors[] = ucfirst(str_replace('_', ' ', $file)) . " is required";
            continue;
        }
        
        $file_name = $_FILES[$file]['name'];
        $file_size = $_FILES[$file]['size'];
        $file_tmp = $_FILES[$file]['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "File type not allowed for " . ucfirst(str_replace('_', ' ', $file));
        }
        
        if ($file_size > $max_file_size) {
            $errors[] = "File size exceeds 2MB limit for " . ucfirst(str_replace('_', ' ', $file));
        }
        
        $file_uploads[$file] = [
            'name' => $file_name,
            'tmp_name' => $file_tmp,
            'size' => $file_size,
            'ext' => $file_ext
        ];
    }
    
    // Optional file: voter_card
    if (isset($_FILES['voter_card']) && $_FILES['voter_card']['error'] == 0) {
        $file_name = $_FILES['voter_card']['name'];
        $file_size = $_FILES['voter_card']['size'];
        $file_tmp = $_FILES['voter_card']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (!in_array($file_ext, $allowed_extensions)) {
            $errors[] = "File type not allowed for Voter Card";
        }
        
        if ($file_size > $max_file_size) {
            $errors[] = "File size exceeds 2MB limit for Voter Card";
        }
        
        $file_uploads['voter_card'] = [
            'name' => $file_name,
            'tmp_name' => $file_tmp,
            'size' => $file_size,
            'ext' => $file_ext
        ];
    }
    
    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        $error_str = implode("<br>", $errors);
        echo "<script>alert('Please fix the following errors:\\n" . str_replace("<br>", "\\n", $error_str) . "'); window.history.back();</script>";
        exit;
    }
    
    // If validation passes, send email
    try {
        // Create a new PHPMailer instance
        $mail = new PHPMailer(true);
        
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'patrasagarika654@gmail.com'; // SMTP username
        $mail->Password = 'dqnk duhw jwxz uydo'; // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        
        // Recipients
        $mail->setFrom('patrasagarika654@gmail.com', 'Grievance Redressal System (PMAY)');
        $mail->addAddress('patrasagarika654@gmail.com'); // Admin email
        $mail->addReplyTo($email, $full_name);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = 'New Grievance Redressal Application from ' . $full_name;
        
        // Email body
        $body = "
        <h2>New Grievance Redressal Application (PMAY)</h2>
        <p><strong>Letter No./Date:</strong> {$letter_no}</p>
        <p><strong>Full Name:</strong> {$full_name}</p>
        <p><strong>Father Name:</strong> {$father_name}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Mobile:</strong> {$mobile}</p>
        <p><strong>Gender:</strong> {$gender}</p>
        <p><strong>District:</strong> {$district}</p>
        <p><strong>Ward:</strong> {$ward}</p>
        <p><strong>Village/Locality:</strong> {$village}</p>
        <p><strong>Pin Code:</strong> {$pin_code}</p>
        <p><strong>Address:</strong> {$address}</p>
        ";
        
        $mail->Body = $body;
        $mail->AltBody = strip_tags($body);
        
        // Attach files
        foreach ($file_uploads as $file_key => $file_data) {
            $mail->addAttachment($file_data['tmp_name'], $file_data['name']);
        }
        
        // Send email
        $mail->send();
        
        // Redirect to success page or show success message
        echo "<script>alert('Your Grievance Redressal application has been submitted successfully!'); window.location.href = 'grievance-redressal.html';</script>";
        
    } catch (Exception $e) {
        echo "<script>alert('There was an error sending your application. Please try again later. Error: " . $mail->ErrorInfo . "'); window.history.back();</script>";
    }
}
?>