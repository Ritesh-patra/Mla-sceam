<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Submitted Successfully</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto max-w-md p-6 mt-10">
        <div class="bg-white rounded-lg shadow-md p-8 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            
            <h1 class="text-2xl font-bold text-gray-800 mb-2">Application Submitted Successfully</h1>
            
            <p class="text-gray-600 mb-4">
                Your Caste Certificate application has been submitted successfully. We will review your application and get back to you soon.
            </p>
            
            <?php if (isset($_GET['ref']) && !empty($_GET['ref'])): ?>
                <div class="bg-gray-100 p-4 rounded-md mb-6">
                    <p class="text-gray-700">Reference Number:</p>
                    <p class="text-lg font-bold text-blue-700"><?php echo htmlspecialchars($_GET['ref']); ?></p>
                    <p class="text-xs text-gray-500 mt-1">Please save  ?></p>
                    <p class="text-xs text-gray-500 mt-1">Please save this reference number for future correspondence</p>
                </div>
            <?php endif; ?>
            
            <a href="index.html" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-6 rounded-md transition duration-200">
                Return to Home
            </a>
        </div>
    </div>
</body>
</html>
