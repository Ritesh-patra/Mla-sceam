<?php
// Set error reporting for debugging (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Function to sanitize form data
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// SMTP Configuration - Using the provided credentials
$smtp_server = 'smtp.gmail.com';  // Gmail SMTP server
$smtp_port = 587;                 // TLS port for Gmail
$smtp_username = 'patrasagarika654@gmail.com';  // Provided email
$smtp_password = 'dqnk duhw jwxz uydo';         // Provided password (this appears to be an app password)
$smtp_from = 'patrasagarika654@gmail.com';      // From email address
$smtp_from_name = 'Caste Certificate System';   // From name
$smtp_to = 'patrasagarika654@gmail.com';                 // Change this to the recipient email

// Initialize response array
$response = [
    'success' => false,
    'message' => ''
];

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Collect and sanitize form data
    $letterNo = isset($_POST['letter-no']) ? sanitize_input($_POST['letter-no']) : '';
    $fullName = isset($_POST['full-name']) ? sanitize_input($_POST['full-name']) : '';
    $fatherName = isset($_POST['father-name']) ? sanitize_input($_POST['father-name']) : '';
    $motherName = isset($_POST['mother-name']) ? sanitize_input($_POST['mother-name']) : '';
    $email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
    $mobile = isset($_POST['mobile']) ? sanitize_input($_POST['mobile']) : '';
    $gender = isset($_POST['gender']) ? sanitize_input($_POST['gender']) : '';
    $district = isset($_POST['district']) ? sanitize_input($_POST['district']) : '';
    $ward = isset($_POST['ward']) ? sanitize_input($_POST['ward']) : '';
    $village = isset($_POST['village']) ? sanitize_input($_POST['village']) : '';
    $pinCode = isset($_POST['pin-code']) ? sanitize_input($_POST['pin-code']) : '';
    $address = isset($_POST['address']) ? sanitize_input($_POST['address']) : '';
    
    // Validate required fields
    $requiredFields = [
        'full-name' => 'Full Name',
        'father-name' => 'Father Name',
        'mother-name' => 'Mother Name',
        'email' => 'Email',
        'mobile' => 'Mobile',
        'district' => 'District',
        'ward' => 'Ward',
        'pin-code' => 'Pin Code',
        'address' => 'Address'
    ];
    
    $errors = [];
    
    foreach ($requiredFields as $field => $label) {
        if (empty($_POST[$field])) {
            $errors[] = "$label is required";
        }
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    // Validate mobile number (10 digits)
    if (!preg_match("/^[0-9]{10}$/", $mobile)) {
        $errors[] = "Mobile number must be 10 digits";
    }
    
    // Validate PIN code (6 digits)
    if (!preg_match("/^[0-9]{6}$/", $pinCode)) {
        $errors[] = "PIN code must be 6 digits";
    }
    
    // Process file uploads
    $uploadedFiles = [];
    $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx'];
    $maxFileSize = 2 * 1024 * 1024; // 2MB
    
    $fileFields = [
        'aadhar-card' => 'Aadhar Card / Passport',
        'passport-photo' => 'Passport Photo',
        'birth-certificate' => 'Birth / School Certificate',
        'pata-ror' => 'Pata / ROR',
        'caste-proof' => 'Caste Proof / Affidavit'
    ];
    
    $requiredFileFields = ['aadhar-card', 'passport-photo', 'pata-ror', 'caste-proof'];
    
    foreach ($fileFields as $field => $label) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] == 0) {
            $file = $_FILES[$field];
            $fileName = $file['name'];
            $fileSize = $file['size'];
            $fileTmpName = $file['tmp_name'];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validate file extension
            if (!in_array($fileExt, $allowedExtensions)) {
                $errors[] = "$label: Only PDF, DOC, DOCX, XLS, XLSX files are allowed";
            }
            
            // Validate file size
            if ($fileSize > $maxFileSize) {
                $errors[] = "$label: File size must be less than 2MB";
            }
            
            // Store file information for email attachment
            if (empty($errors)) {
                $uploadedFiles[$field] = [
                    'name' => $fileName,
                    'tmp_name' => $fileTmpName,
                    'type' => $file['type']
                ];
            }
        } else {
            if (in_array($field, $requiredFileFields) && $_FILES[$field]['error'] != UPLOAD_ERR_NO_FILE) {
                $errors[] = "Error uploading $label";
            } elseif (in_array($field, $requiredFileFields) && empty($_FILES[$field]['name'])) {
                $errors[] = "$label is required";
            }
        }
    }
    
    // If no errors, proceed with sending email
    if (empty($errors)) {
        // Generate a unique boundary for multipart email
        $boundary = md5(time());
        
        // Email subject
        $subject = "Caste Certificate Application: $letterNo";
        
        // Create email body
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/html; charset=UTF-8\r\n";
        $body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
        $body .= "<html><body>";
        $body .= "<h2>Caste Certificate Application Details</h2>";
        $body .= "<p><strong>Letter No./Date:</strong> $letterNo</p>";
        $body .= "<p><strong>Full Name:</strong> $fullName</p>";
        $body .= "<p><strong>Father Name:</strong> $fatherName</p>";
        $body .= "<p><strong>Mother Name:</strong> $motherName</p>";
        $body .= "<p><strong>Email:</strong> $email</p>";
        $body .= "<p><strong>Mobile:</strong> $mobile</p>";
        $body .= "<p><strong>Gender:</strong> " . ucfirst($gender) . "</p>";
        $body .= "<p><strong>District:</strong> $district</p>";
        $body .= "<p><strong>Ward:</strong> $ward</p>";
        $body .= "<p><strong>Grampanchayat:</strong> $grampanchayat</p>";
        $body .= "<p><strong>Village/Locality:</strong> $village</p>";
        $body .= "<p><strong>Pin Code:</strong> $pinCode</p>";
        $body .= "<p><strong>Address:</strong> $address</p>";
        $body .= "</body></html>\r\n";
        
        // Add attachments
        foreach ($uploadedFiles as $field => $file) {
            $fileContent = file_get_contents($file['tmp_name']);
            $body .= "--$boundary\r\n";
            $body .= "Content-Type: " . $file['type'] . "; name=\"" . $file['name'] . "\"\r\n";
            $body .= "Content-Transfer-Encoding: base64\r\n";
            $body .= "Content-Disposition: attachment; filename=\"" . $file['name'] . "\"\r\n\r\n";
            $body .= chunk_split(base64_encode($fileContent)) . "\r\n";
        }
        
        $body .= "--$boundary--";
        
        // Try to send email using custom SMTP function
        $result = smtp_mail($smtp_to, $subject, $body, $boundary, $smtp_from, $smtp_from_name);
        
        if ($result) {
            $response['success'] = true;
            $response['message'] = "Your application has been submitted successfully. Reference Number: $letterNo";
            
            // Redirect to success page or show success message
            header("Location: success.php?ref=$letterNo");
            exit();
        } else {
            $response['message'] = "Failed to send email. Please try again later.";
        }
    } else {
        $response['message'] = implode("<br>", $errors);
    }
}

// Custom SMTP mail function
function smtp_mail($to, $subject, $body, $boundary, $from_email, $from_name) {
    global $smtp_server, $smtp_port, $smtp_username, $smtp_password;
    
    try {
        // Connect to SMTP server
        $socket = fsockopen($smtp_server, $smtp_port, $errno, $errstr, 30);
        
        if (!$socket) {
            error_log("SMTP Error: $errstr ($errno)");
            return false;
        }
        
        // Check connection
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '220') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Send EHLO command
        fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Clear any additional EHLO response lines
        while (substr($response, 3, 1) == '-') {
            $response = fgets($socket, 515);
        }
        
        // Start TLS if using port 587
        if ($smtp_port == 587) {
            fputs($socket, "STARTTLS\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '220') {
                error_log("SMTP Error: " . $response);
                return false;
            }
            
            // Enable crypto
            stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
            
            // Send EHLO again after TLS
            fputs($socket, "EHLO " . $_SERVER['SERVER_NAME'] . "\r\n");
            $response = fgets($socket, 515);
            if (substr($response, 0, 3) != '250') {
                error_log("SMTP Error: " . $response);
                return false;
            }
            
            // Clear any additional EHLO response lines
            while (substr($response, 3, 1) == '-') {
                $response = fgets($socket, 515);
            }
        }
        
        // Authenticate
        fputs($socket, "AUTH LOGIN\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        fputs($socket, base64_encode($smtp_username) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '334') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        fputs($socket, base64_encode($smtp_password) . "\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '235') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Set sender
        fputs($socket, "MAIL FROM: <$from_email>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Set recipient
        fputs($socket, "RCPT TO: <$to>\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Send DATA command
        fputs($socket, "DATA\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '354') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Construct email headers
        $headers = "From: $from_name <$from_email>\r\n";
        $headers .= "To: <$to>\r\n";
        $headers .= "Subject: $subject\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion() . "\r\n";
        $headers .= "Date: " . date("r") . "\r\n";
        
        // Send email headers and body
        fputs($socket, $headers . "\r\n" . $body . "\r\n.\r\n");
        $response = fgets($socket, 515);
        if (substr($response, 0, 3) != '250') {
            error_log("SMTP Error: " . $response);
            return false;
        }
        
        // Close connection
        fputs($socket, "QUIT\r\n");
        fclose($socket);
        
        return true;
    } catch (Exception $e) {
        error_log("SMTP Exception: " . $e->getMessage());
        return false;
    }
}

// If AJAX request, return JSON response
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// If not AJAX and there's an error, redirect back to form with error message
if (!$response['success'] && !empty($response['message'])) {
    header("Location: index.html?error=" . urlencode($response['message']));
    exit();
}
?>