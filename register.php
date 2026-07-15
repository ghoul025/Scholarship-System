<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
    // Sanitize inputs
    $username       = trim($_POST['username'] ?? '');

    // Normalize names to title case (server-side) to ensure consistent storage.
    function to_title_case($s) {
      $s = trim((string)$s);
      if ($s === '') return '';
      if (function_exists('mb_convert_case')) {
        return mb_convert_case($s, MB_CASE_TITLE, 'UTF-8');
      }
      return ucwords(strtolower($s));
    }

    $first_name     = to_title_case($_POST['first_name'] ?? '');
    $middle_name    = to_title_case($_POST['middle_name'] ?? '');
    $last_name      = to_title_case($_POST['last_name'] ?? '');
    $extended_name  = to_title_case($_POST['extended_name'] ?? '');
        $email          = trim($_POST['email'] ?? '');
        $phone          = trim($_POST['phone'] ?? '');
        $sex            = trim($_POST['sex'] ?? '');
        $units          = intval($_POST['units'] ?? 0);
        $tuition_fee    = floatval($_POST['tuition_fee'] ?? 0);
  $course         = trim($_POST['course'] ?? '');
  $year_level     = trim($_POST['year_level'] ?? '');
  $scholarship_type = trim($_POST['scholarship_type'] ?? '');
        $batch_input    = trim($_POST['batch'] ?? '');

        // Validation
        if (
            !$username || !$first_name || !$last_name || !$email ||
            !$phone || !$sex || !$units || !$tuition_fee || !$course ||
            !$year_level || !$scholarship_type || !$batch_input
        ) {
            throw new Exception("All required fields must be filled.");
        }

        // Email validation (must be valid and end with @gmail.com)
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/@gmail\.com$/', $email)) {
            throw new Exception("Email must be a valid Gmail address (e.g., user@gmail.com).");
        }

        // Phone validation (Philippines: 11 digits, starts with 09)
        if (!preg_match('/^09[0-9]{9}$/', $phone)) {
            throw new Exception("Invalid phone number format. Example: 09123456789");
        }

        // Batch validation (required, must be number or decimal with up to 2 places)
        if (!preg_match('/^\d+(\.\d{1,2})?$/', $batch_input)) {
            throw new Exception("Batch must be a number (e.g., 13 or 13.5).");
        }
        $batch = number_format((float)$batch_input, 2, '.', '');

    // Prevent Duplicate Applications / Accounts
    // Check username across users, scholars, and existing applications
    $checkUser = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    $checkUser->execute([$username]);
    if ($checkUser->fetch()) {
      throw new Exception("Username already in use. Please choose a different username.");
    }

    // Check email across scholars and applications
    $checkEmailScholar = $conn->prepare("SELECT id FROM scholars WHERE email = ? LIMIT 1");
    $checkEmailScholar->execute([$email]);
    if ($checkEmailScholar->fetch()) {
      throw new Exception("Email already registered to an existing scholar. If you believe this is an error contact the administrator.");
    }

    $checkEmailApp = $conn->prepare("SELECT id FROM scholar_applications WHERE email = ? LIMIT 1");
    $checkEmailApp->execute([$email]);
    if ($checkEmailApp->fetch()) {
      throw new Exception("An application using this email has already been submitted.");
    }

    // Check phone across scholars and applications
    $checkPhoneScholar = $conn->prepare("SELECT id FROM scholars WHERE phone = ? LIMIT 1");
    $checkPhoneScholar->execute([$phone]);
    if ($checkPhoneScholar->fetch()) {
      throw new Exception("Phone number already registered to an existing scholar.");
    }

    $checkPhoneApp = $conn->prepare("SELECT id FROM scholar_applications WHERE phone = ? LIMIT 1");
    $checkPhoneApp->execute([$phone]);
    if ($checkPhoneApp->fetch()) {
      throw new Exception("An application using this phone number has already been submitted.");
    }

    // Final application duplicate check by username across applications (just in case)
    $checkAppUser = $conn->prepare("SELECT id FROM scholar_applications WHERE username = ? LIMIT 1");
    $checkAppUser->execute([$username]);
    if ($checkAppUser->fetch()) {
      throw new Exception("An application with this username already exists.");
    }

        // Insert Application
        $stmt = $conn->prepare("
            INSERT INTO scholar_applications 
            (username, first_name, middle_name, last_name, extended_name, email, phone, sex, units, tuition_fee, course, year_level, scholarship_type, batch) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $username, $first_name, $middle_name ?: null, $last_name,
            $extended_name ?: null, $email, $phone, $sex, $units,
            $tuition_fee, $course, $year_level, $scholarship_type, $batch
        ]);

        $_SESSION['application_message'] = "✅ Your application has been submitted and is pending admin approval.";
        $_SESSION['application_status'] = "success";

    } catch (Exception $e) {
        $_SESSION['application_message'] = "❌ " . $e->getMessage();
        $_SESSION['application_status'] = "error";
    }

    header("Location: index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Scholar Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <style>
      @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(10px);} 
        to { opacity: 1; transform: translateY(0);} 
      }
      .animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
    </style>
    <script>
      function restrictToNumbers(event) {
        // Allow only digits (0-9), backspace, and navigation keys
        return (event.charCode != 8 && event.charCode == 0) || 
               (event.charCode >= 48 && event.charCode <= 57);
      }
    </script>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div class="max-w-md w-full bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden animate-fadeIn">
        <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white text-center py-5 font-semibold text-lg flex items-center justify-center gap-2">
          <i class="fa fa-user-plus text-xl"></i> Scholar Registration
        </div>
        <div class="p-6 space-y-4">
          <form method="POST" action="" autocomplete="off" class="grid grid-cols-1 gap-3">
            <div>
              <label class="block text-sm font-medium text-gray-700">First Name</label>
              <input type="text" name="first_name" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Middle Name (Optional)</label>
              <input type="text" name="middle_name" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Last Name</label>
              <input type="text" name="last_name" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Extended Name (Optional)</label>
              <input type="text" name="extended_name" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Email (Gmail only)</label>
              <input type="email" name="email" required pattern="[a-zA-Z0-9._%+-]+@gmail\.com" placeholder="e.g. user@gmail.com" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Username</label>
              <input type="text" name="username" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Phone Number</label>
              <input type="tel" name="phone" required pattern="[0-9]{11}" maxlength="11" placeholder="e.g. 09123456789" onkeypress="return restrictToNumbers(event)" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Sex</label>
              <select name="sex" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Units</label>
              <input type="number" name="units" required min="1" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Tuition Fee</label>
              <input type="number" name="tuition_fee" required min="0" step="0.01" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Course</label>
              <select name="course" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Course</option>
                <option value="BSCS">BSCS</option>
                <option value="BSA">BSA</option>
                <option value="BSHM">BSHM</option>
                <option value="BSBA">BSBA</option>
                <option value="BSTM">BSTM</option>
                <option value="BEED">BEED</option>
                <option value="BSED">BSED</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Year Level</label>
              <select name="year_level" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Year Level</option>
                <option value="1st Year">1st Year</option>
                <option value="2nd Year">2nd Year</option>
                <option value="3rd Year">3rd Year</option>
                <option value="4th Year">4th Year</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Scholarship Type</label>
              <select name="scholarship_type" required class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Scholarship Type</option>
                <option value="TES">TES</option>
                <option value="TDP">TDP</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700">Batch (number or decimal)</label>
              <input type="number" name="batch" required min="1" step="0.1" placeholder="e.g. 13 or 13.5" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded shadow hover:bg-blue-700 transition flex items-center justify-center gap-2">
              <i class="fa fa-paper-plane"></i> Submit Application
            </button>
          </form>
          <p class="text-gray-600 text-center text-sm mt-4">
            Already have an account?  
            <a href="index.php" class="text-blue-600 font-semibold hover:underline">Log In</a>
          </p>
        </div>
      </div>
    </div>
    <?php include __DIR__ . '/includes/titlecase_inputs.php'; ?>
</body>
</html>