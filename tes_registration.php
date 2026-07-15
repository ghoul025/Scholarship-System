<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'config.php';

// Helper function for title case, preserving spaces for names
if (!function_exists('titleCase')) {
  function titleCase($string) {
    return mb_convert_case(
      preg_replace('/\s+/u', ' ', $string), // Normalize multiple spaces to single
      MB_CASE_TITLE,
      'UTF-8'
    );
  }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Sanitize inputs, preserving spaces where appropriate
        $student_id = preg_replace('/\s+/u', ' ', trim($_POST['student_id'] ?? '')); // Allow single spaces
        $last_name = titleCase(trim($_POST['last_name'] ?? ''));
        $given_name = titleCase(trim($_POST['given_name'] ?? ''));
        $extension_name = titleCase(trim($_POST['extension_name'] ?? ''));
        $middle_name = titleCase(trim($_POST['middle_name'] ?? ''));
        $sex = trim($_POST['sex'] ?? '');
        $birthdate = trim($_POST['birthdate'] ?? '');
        $complete_program_name = preg_replace('/\s+/u', ' ', trim($_POST['complete_program_name'] ?? '')); // Allow spaces
        $year_level = intval($_POST['year_level'] ?? 0);
        $father_last_name = titleCase(trim($_POST['father_last_name'] ?? ''));
        $father_given_name = titleCase(trim($_POST['father_given_name'] ?? ''));
        $father_middle_name = titleCase(trim($_POST['father_middle_name'] ?? ''));
        $mother_last_name = titleCase(trim($_POST['mother_last_name'] ?? ''));
        $mother_given_name = titleCase(trim($_POST['mother_given_name'] ?? ''));
        $mother_middle_name = titleCase(trim($_POST['mother_middle_name'] ?? ''));
        $street = preg_replace('/\s+/u', ' ', trim($_POST['street'] ?? '')); // Allow spaces
        $barangay = trim($_POST['barangay'] ?? '');
        $zip_code = trim($_POST['zip_code'] ?? '');
        $disability = preg_replace('/\s+/u', ' ', trim($_POST['disability'] ?? '')); // Allow spaces
        $contact_number = trim($_POST['contact_number'] ?? '');
        $email_address = trim($_POST['email_address'] ?? '');
        $indigenous_people_group = preg_replace('/\s+/u', ' ', trim($_POST['indigenous_people_group'] ?? '')); // Allow spaces

        // Combine street and barangay for storage
        $street_barangay = trim($street . ($street && $barangay ? ', ' : '') . $barangay);

        // Validation for required fields
        if (
            !$student_id || !$last_name || !$given_name || !$middle_name ||
            !$sex || !$birthdate || !$complete_program_name || !$year_level ||
            !$father_last_name || !$father_given_name || !$father_middle_name ||
            !$mother_last_name || !$mother_given_name || !$mother_middle_name ||
            !$street_barangay || !$zip_code || !$contact_number || !$email_address
        ) {
            throw new Exception("All required fields must be filled.");
        }

        // Validate no numbers in name fields
        $name_fields = [
            'last_name' => $last_name,
            'given_name' => $given_name,
            'middle_name' => $middle_name,
            'father_last_name' => $father_last_name,
            'father_given_name' => $father_given_name,
            'father_middle_name' => $father_middle_name,
            'mother_last_name' => $mother_last_name,
            'mother_given_name' => $mother_given_name,
            'mother_middle_name' => $mother_middle_name
        ];
        foreach ($name_fields as $field => $value) {
            if (preg_match('/\d/u', $value)) {
                throw new Exception("Numbers are not allowed in $field.");
            }
        }

        // Student ID validation (alphanumeric and spaces)
        if (!preg_match('/^[a-zA-Z0-9\s]+$/', $student_id)) {
            throw new Exception("Student ID can only contain letters, numbers, and spaces.");
        }

        // Complete program name, street, disability, indigenous people group (allow letters, numbers, spaces)
        $text_fields = [
            'complete_program_name' => $complete_program_name,
            'street' => $street,
            'disability' => $disability,
            'indigenous_people_group' => $indigenous_people_group
        ];
        foreach ($text_fields as $field => $value) {
            if ($value && !preg_match('/^[a-zA-Z0-9\s,.()-]+$/', $value)) {
                throw new Exception("$field can only contain letters, numbers, spaces, and common punctuation (e.g., commas, periods, parentheses).");
            }
        }

        // Phone validation (Philippines: 11 digits, starts with 09, no spaces)
        if (!preg_match('/^09[0-9]{9}$/', $contact_number)) {
            throw new Exception("Invalid phone number format. Must be 11 digits starting with 09 (e.g., 09123456789).");
        }

        // Email validation (no spaces allowed)
        if (!filter_var($email_address, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email address format.");
        }

        // Birthdate validation (YYYY-MM-DD)
        $date = DateTime::createFromFormat('Y-m-d', $birthdate);
        if (!$date) {
            throw new Exception("Invalid birthdate. Please select a valid date.");
        }
        $mysql_birthdate = $date->format('Y-m-d');

        // Year level validation
        if ($year_level < 1 || $year_level > 4) {
            throw new Exception("Year level must be between 1 and 4.");
        }

        // Sex validation
        if (!in_array($sex, ['Male', 'Female'])) {
            throw new Exception("Sex must be either Male or Female.");
        }

        // Zip code validation (4-6 digits, no spaces)
        if (!preg_match('/^\d{4,6}$/', $zip_code)) {
            throw new Exception("Zip code must be 4 to 6 digits.");
        }

        // Prevent duplicate applications
        $check = $conn->prepare("SELECT student_id FROM tes_applicants WHERE student_id = ? OR contact_number = ? OR email_address = ? LIMIT 1");
        $check->execute([$student_id, $contact_number, $email_address]);
        if ($check->fetch()) {
            throw new Exception("An application with this student ID, contact number, or email address already exists.");
        }

        // Insert application
        $stmt = $conn->prepare("
            INSERT INTO tes_applicants 
            (student_id, last_name, given_name, extension_name, middle_name, sex, birthdate, 
             complete_program_name, year_level, father_last_name, father_given_name, father_middle_name, 
             mother_last_name, mother_given_name, mother_middle_name, street_barangay, zip_code, 
             disability, contact_number, email_address, indigenous_people_group) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $student_id, $last_name, $given_name, $extension_name ?: null, $middle_name, $sex, $mysql_birthdate,
            $complete_program_name, $year_level, $father_last_name, $father_given_name, $father_middle_name,
            $mother_last_name, $mother_given_name, $mother_middle_name, $street_barangay, $zip_code,
            $disability ?: null, $contact_number, $email_address, $indigenous_people_group ?: null
        ]);

        $_SESSION['application_message'] = "✅ Your TES application has been submitted and is pending admin approval.";
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
    <title>TES Applicant Registration</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <!-- flatpickr datepicker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
      @keyframes fadeIn { 
        from { opacity: 0; transform: translateY(10px);} 
        to { opacity: 1; transform: translateY(0);} 
      }
      .animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
      @keyframes shake {
        0%, 100% { transform: translateX(0); }
        25% { transform: translateX(-5px); }
        75% { transform: translateX(5px); }
      }
      .shake { animation: shake 0.5s; }
      .submitting { justify-content: center !important; }
      input:focus, select:focus {
        outline: 2px solid #3b82f6;
        outline-offset: 2px;
      }
      .valid { border-color: #10b981 !important; }
      .invalid { border-color: #ef4444 !important; }
    </style>
</head>
<body class="bg-gray-50 text-gray-800">
    <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
      <div class="max-w-md w-full bg-white border border-gray-200 shadow-lg rounded-lg overflow-hidden animate-fadeIn">
        <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white text-center py-5 font-semibold text-lg flex items-center justify-center gap-2">
          <i class="fa fa-user-plus text-xl"></i> TES Applicant Registration
        </div>
        <div class="p-6 space-y-4">
          <form id="tesForm" method="POST" action="" autocomplete="off" class="grid grid-cols-1 gap-3" novalidate>
            <div>
              <label for="student_id" class="block text-sm font-medium text-gray-700">Student ID</label>
              <input type="text" id="student_id" name="student_id" required aria-describedby="student_id_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="50">
              <p id="student_id_help" class="error-message text-red-600 text-xs mt-1 hidden">Student ID is required and can only contain letters, numbers, and spaces.</p>
            </div>
            <div>
              <label for="last_name" class="block text-sm font-medium text-gray-700">Last Name</label>
              <input type="text" id="last_name" name="last_name" required aria-describedby="last_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="last_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Last Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="given_name" class="block text-sm font-medium text-gray-700">Given Name</label>
              <input type="text" id="given_name" name="given_name" required aria-describedby="given_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="given_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Given Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="extension_name" class="block text-sm font-medium text-gray-700">Extension Name (Optional)</label>
              <input type="text" id="extension_name" name="extension_name" aria-describedby="extension_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="50">
              <p id="extension_name_help" class="error-message text-red-600 text-xs mt-1 hidden sr-only">Extension Name can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="middle_name" class="block text-sm font-medium text-gray-700">Middle Name</label>
              <input type="text" id="middle_name" name="middle_name" required aria-describedby="middle_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="middle_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Middle Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="sex" class="block text-sm font-medium text-gray-700">Sex</label>
              <select id="sex" name="sex" required aria-describedby="sex_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Sex</option>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
              </select>
              <p id="sex_help" class="error-message text-red-600 text-xs mt-1 hidden">Sex is required.</p>
            </div>
            <div>
              <label for="birthdate" class="block text-sm font-medium text-gray-700">Birthdate</label>
              <input type="date" id="birthdate" name="birthdate" required aria-describedby="birthdate_help" min="1900-01-01" max="<?php echo date('Y-m-d'); ?>" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <p id="birthdate_help" class="error-message text-red-600 text-xs mt-1 hidden">Birthdate is required and must be a valid date.</p>
            </div>
            <div>
              <label for="complete_program_name" class="block text-sm font-medium text-gray-700">Complete Program Name</label>
              <select id="complete_program_name" name="complete_program_name" required aria-describedby="complete_program_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="Bachelor of Science in Computer Science">Bachelor of Science in Computer Science</option>
                <option value="Bachelor of Science in Tourism Management">Bachelor of Science in Tourism Management</option>
                <option value="Bachelor of Science in Business Administration">Bachelor of Science in Business Administration</option>
                <option value="Bachelor of Science in Accountancy">Bachelor of Science in Accountancy</option>
                <option value="Bachelor of Elementary Education">Bachelor of Elementary Education</option>
                <option value="Bachelor of Science in Hospitality Management">Bachelor of Science in Hospitality Management</option>
                <option value="Bachelor of Science in Education">Bachelor of Science in Education</option>
                <!-- Options will be added later by admin/user -->
              </select>
              <p id="complete_program_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Complete Program Name is required.</p>
            </div>
            <div>
              <label for="year_level" class="block text-sm font-medium text-gray-700">Year Level</label>
              <select id="year_level" name="year_level" required aria-describedby="year_level_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Select Year Level</option>
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
              </select>
              <p id="year_level_help" class="error-message text-red-600 text-xs mt-1 hidden">Year Level is required.</p>
            </div>
            <div>
              <label for="father_last_name" class="block text-sm font-medium text-gray-700">Father's Last Name</label>
              <input type="text" id="father_last_name" name="father_last_name" required aria-describedby="father_last_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="father_last_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Father's Last Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="father_given_name" class="block text-sm font-medium text-gray-700">Father's Given Name</label>
              <input type="text" id="father_given_name" name="father_given_name" required aria-describedby="father_given_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="father_given_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Father's Given Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="father_middle_name" class="block text-sm font-medium text-gray-700">Father's Middle Name</label>
              <input type="text" id="father_middle_name" name="father_middle_name" required aria-describedby="father_middle_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="father_middle_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Father's Middle Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="mother_last_name" class="block text-sm font-medium text-gray-700">Mother's Maiden Last Name</label>
              <input type="text" id="mother_last_name" name="mother_last_name" required aria-describedby="mother_last_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="mother_last_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Mother's Maiden Last Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="mother_given_name" class="block text-sm font-medium text-gray-700">Mother's Maiden Given Name</label>
              <input type="text" id="mother_given_name" name="mother_given_name" required aria-describedby="mother_given_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="mother_given_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Mother's Maiden Given Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="mother_middle_name" class="block text-sm font-medium text-gray-700">Mother's Maiden Middle Name</label>
              <input type="text" id="mother_middle_name" name="mother_middle_name" required aria-describedby="mother_middle_name_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="mother_middle_name_help" class="error-message text-red-600 text-xs mt-1 hidden">Mother's Maiden Middle Name is required and can only contain letters and spaces.</p>
            </div>
            <div>
              <label for="street" class="block text-sm font-medium text-gray-700">Street Address</label>
              <input type="text" id="street" name="street" required aria-describedby="street_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="street_help" class="error-message text-red-600 text-xs mt-1 hidden">Street Address is required and can only contain letters, numbers, spaces, and common punctuation.</p>
            </div>
            <div>
              <label for="zip_code" class="block text-sm font-medium text-gray-700">Zip Code</label>
              <input type="text" id="zip_code" name="zip_code" required pattern="\d{4,6}" maxlength="6" placeholder="e.g. 4213" aria-describedby="zip_code_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <p id="zip_code_help" class="error-message text-red-600 text-xs mt-1 hidden">Zip code must be 4 to 6 digits.</p>
            </div>
            <div>
              <label for="barangay" class="block text-sm font-medium text-gray-700">Barangay</label>
              <input type="text" id="barangay" name="barangay" required aria-describedby="barangay_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="barangay_help" class="error-message text-red-600 text-xs mt-1 hidden">Barangay is required and can only contain letters, numbers, spaces, and common punctuation.</p>
            </div>
            <div>
              <label for="disability" class="block text-sm font-medium text-gray-700">Disability (Optional)</label>
              <input type="text" id="disability" name="disability" aria-describedby="disability_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="disability_help" class="error-message text-red-600 text-xs mt-1 hidden sr-only">Disability can only contain letters, numbers, spaces, and common punctuation.</p>
            </div>
            <div>
              <label for="contact_number" class="block text-sm font-medium text-gray-700">Contact Number</label>
              <input type="tel" id="contact_number" name="contact_number" required pattern="09\d{9}" maxlength="11" placeholder="e.g. 09123456789" aria-describedby="contact_number_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <p id="contact_number_help" class="error-message text-red-600 text-xs mt-1 hidden">Contact number must be 11 digits starting with 09.</p>
            </div>
            <div>
              <label for="email_address" class="block text-sm font-medium text-gray-700">Email Address</label>
              <input type="email" id="email_address" name="email_address" required aria-describedby="email_address_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
              <p id="email_address_help" class="error-message text-red-600 text-xs mt-1 hidden">Email address is required and must be valid.</p>
            </div>
            <div>
              <label for="indigenous_people_group" class="block text-sm font-medium text-gray-700">Indigenous People Group (Optional)</label>
              <input type="text" id="indigenous_people_group" name="indigenous_people_group" aria-describedby="indigenous_people_group_help" class="mt-1 block w-full border-gray-300 rounded shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500" maxlength="100">
              <p id="indigenous_people_group_help" class="error-message text-red-600 text-xs mt-1 hidden sr-only">Indigenous People Group can only contain letters, numbers, spaces, and common punctuation.</p>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-semibold py-2 rounded shadow hover:bg-blue-700 transition flex items-center justify-center gap-2">
              <span class="btn-text"><i class="fa fa-paper-plane"></i> Submit Application</span>
              <div class="spinner-border w-5 h-5 border-2 border-white rounded-full animate-spin hidden"></div>
            </button>
          </form>
          <p class="text-gray-600 text-center text-sm mt-4">
            Already have an account?  
            <a href="index.php" class="text-blue-600 font-semibold hover:underline">Log In</a>
          </p>
        </div>
      </div>
    </div>
    <script>
      // Polyfill for older browsers
      if (!String.prototype.trim) {
        String.prototype.trim = function () {
          return this.replace(/^\s+|\s+$/g, '');
        };
      }

      // Main DOM elements and helpers
      const form = document.getElementById('tesForm');
      const submitBtn = form ? form.querySelector('button[type="submit"]') : null;
      const btnText = submitBtn ? submitBtn.querySelector('.btn-text') : null;
      const spinner = submitBtn ? submitBtn.querySelector('.spinner-border') : null;

      const nameFields = ['last_name','given_name','middle_name',
        'father_last_name','father_given_name','father_middle_name',
        'mother_last_name','mother_given_name','mother_middle_name'
      ];

      // Text fields: Allow letters, numbers, spaces, and common punctuation
      const textFields = ['student_id', 'complete_program_name', 'street', 'barangay', 'disability', 'indigenous_people_group'];
      
      // Name fields validation
      nameFields.forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
          // Allow space key during typing
          input.addEventListener('keydown', function(e) {
            if (e.key === ' ' || e.keyCode === 32) {
              // Allow space if not at start or after another space
              const value = this.value;
              const cursorPos = this.selectionStart;
              if (cursorPos === 0 || value[cursorPos - 1] === ' ') {
                e.preventDefault();
              }
            }
          });

          // Clean input and validate on blur
          input.addEventListener('blur', function() {
            const value = this.value;
            const cleaned = value
              .replace(/[^a-zA-Z\s]/g, '') // Allow letters and spaces
              .replace(/\s+/g, ' ') // Normalize multiple spaces
              .trim()
              .replace(/\b\w/g, c => c.toUpperCase()); // Title case each word
            this.value = cleaned;

            const helpId = `${name}_help`;
            const error = document.getElementById(helpId);
            if (value && !/^[a-zA-Z\s]+$/.test(value)) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} can only contain letters and spaces.`;
              error.classList.remove('hidden');
            } else if (input.required && !value.trim()) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} is required.`;
              error.classList.remove('hidden');
            } else {
              this.classList.add('valid');
              this.classList.remove('invalid');
              error.classList.add('hidden');
            }
          });

          // Real-time validation for feedback
          input.addEventListener('input', function() {
            const value = this.value;
            const helpId = `${name}_help`;
            const error = document.getElementById(helpId);
            if (value && !/^[a-zA-Z\s]*$/.test(value)) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} can only contain letters and spaces.`;
              error.classList.remove('hidden');
            } else if (input.required && !value.trim()) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} is required.`;
              error.classList.remove('hidden');
            } else {
              this.classList.add('valid');
              this.classList.remove('invalid');
              error.classList.add('hidden');
            }
          });

          // Handle paste events
          input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste
              .replace(/[^a-zA-Z\s]/g, '')
              .replace(/\s+/g, ' ')
              .trim()
              .replace(/\b\w/g, c => c.toUpperCase());
            document.execCommand('insertText', false, cleaned);
          });
        }
      });

      // Text fields validation
      textFields.forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
          input.addEventListener('input', function() {
            const value = this.value;
            const cleaned = value
              .replace(/[^a-zA-Z0-9\s,.()-]/g, '') // Allow letters, numbers, spaces, and common punctuation
              .replace(/\s+/g, ' ') // Normalize multiple spaces
              .trim();
            this.value = cleaned;

            const helpId = `${name}_help`;
            const error = document.getElementById(helpId);
            if (value && !/^[a-zA-Z0-9\s,.()-]+$/.test(value)) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} can only contain letters, numbers, spaces, and common punctuation.`;
              error.classList.remove('hidden');
            } else if (input.required && !value.trim()) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} is required.`;
              error.classList.remove('hidden');
            } else {
              this.classList.add('valid');
              this.classList.remove('invalid');
              error.classList.add('hidden');
            }
          });
          input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste
              .replace(/[^a-zA-Z0-9\s,.()-]/g, '')
              .replace(/\s+/g, ' ')
              .trim();
            document.execCommand('insertText', false, cleaned);
          });
          input.addEventListener('blur', function() {
            if (!this.value.trim() && this.required) {
              const helpId = `${name}_help`;
              const error = document.getElementById(helpId);
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.textContent = `${this.previousElementSibling.textContent.trim()} is required.`;
              error.classList.remove('hidden');
            }
          });
        }
      });

      // Numeric fields: Prevent invalid input (no spaces)
      const numericFields = ['contact_number', 'zip_code'];
      numericFields.forEach(name => {
        const input = form.querySelector(`[name="${name}"]`);
        if (input) {
          input.addEventListener('input', function() {
            this.value = this.value.replace(/[^0-9]/g, '');
            const helpId = `${name}_help`;
            const error = document.getElementById(helpId);
            if (name === 'contact_number' && this.value && !/^09\d{0,9}$/.test(this.value)) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.classList.remove('hidden');
            } else if (name === 'zip_code' && this.value && !/^\d{4,6}$/.test(this.value)) {
              this.classList.add('invalid');
              this.classList.remove('valid');
              error.classList.remove('hidden');
            } else {
              this.classList.add('valid');
              this.classList.remove('invalid');
              error.classList.add('hidden');
            }
          });
          input.addEventListener('paste', function(e) {
            e.preventDefault();
            const paste = (e.clipboardData || window.clipboardData).getData('text');
            const cleaned = paste.replace(/[^0-9]/g, '');
            document.execCommand('insertText', false, cleaned);
          });
        }
      });

      // Email field: No spaces, basic email validation
      const emailInput = form.querySelector('#email_address');
      if (emailInput) {
        emailInput.addEventListener('input', function() {
          const value = this.value.trim();
          const helpId = 'email_address_help';
          const error = document.getElementById(helpId);
          if (value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
            this.classList.add('invalid');
            this.classList.remove('valid');
            error.classList.remove('hidden');
          } else if (!value) {
            this.classList.add('invalid');
            this.classList.remove('valid');
            error.textContent = 'Email address is required.';
            error.classList.remove('hidden');
          } else {
            this.classList.add('valid');
            this.classList.remove('invalid');
            error.classList.add('hidden');
          }
        });
        emailInput.addEventListener('paste', function(e) {
          e.preventDefault();
          const paste = (e.clipboardData || window.clipboardData).getData('text');
          const cleaned = paste.trim();
          document.execCommand('insertText', false, cleaned);
        });
      }

      // Client-side validation on submit
      form.addEventListener('submit', function(e) {
        e.preventDefault();
        let isValid = true;
        const errors = form.querySelectorAll('.error-message');
        errors.forEach(error => {
          error.classList.add('hidden');
          error.textContent = error.dataset.default || '';
        });

        // Set default messages
        const fieldDefaults = {
          'student_id_help': 'Student ID is required and can only contain letters, numbers, and spaces.',
          'last_name_help': 'Last Name is required and can only contain letters and spaces.',
          'given_name_help': 'Given Name is required and can only contain letters and spaces.',
          'middle_name_help': 'Middle Name is required and can only contain letters and spaces.',
          'extension_name_help': 'Extension Name can only contain letters and spaces.',
          'sex_help': 'Sex is required.',
          'birthdate_help': 'Birthdate is required and must be a valid date.',
          'complete_program_name_help': 'Complete Program Name is required and can only contain letters, numbers, spaces, and common punctuation.',
          'year_level_help': 'Year Level is required.',
          'father_last_name_help': "Father's Last Name is required and can only contain letters and spaces.",
          'father_given_name_help': "Father's Given Name is required and can only contain letters and spaces.",
          'father_middle_name_help': "Father's Middle Name is required and can only contain letters and spaces.",
          'mother_last_name_help': "Mother's Maiden Last Name is required and can only contain letters and spaces.",
          'mother_given_name_help': "Mother's Maiden Given Name is required and can only contain letters and spaces.",
          'mother_middle_name_help': "Mother's Maiden Middle Name is required and can only contain letters and spaces.",
          'street_help': 'Street Address is required and can only contain letters, numbers, spaces, and common punctuation.',
          'zip_code_help': 'Zip code must be 4 to 6 digits.',
          'barangay_help': 'Barangay is required and can only contain letters, numbers, spaces, and common punctuation.',
          'contact_number_help': 'Contact number must be 11 digits starting with 09.',
          'email_address_help': 'Email address is required and must be valid.',
          'indigenous_people_group_help': 'Indigenous People Group can only contain letters, numbers, spaces, and common punctuation.',
          'disability_help': 'Disability can only contain letters, numbers, spaces, and common punctuation.'
        };
        Object.keys(fieldDefaults).forEach(id => {
          const el = document.getElementById(id);
          if (el) el.dataset.default = fieldDefaults[id];
        });

        // Required fields
        const requiredFields = [
          'student_id', 'last_name', 'given_name', 'middle_name', 'sex',
          'birthdate', 'complete_program_name', 'year_level', 'father_last_name',
          'father_given_name', 'father_middle_name', 'mother_last_name',
          'mother_given_name', 'mother_middle_name', 'street',
          'zip_code', 'barangay', 'contact_number', 'email_address'
        ];
        requiredFields.forEach(name => {
          const input = form.querySelector(`[name="${name}"]`);
          const helpId = `${name}_help`;
          const error = document.getElementById(helpId);
          if (input && !input.value.trim()) {
            error.textContent = `${input.previousElementSibling.textContent.trim()} is required.`;
            error.classList.remove('hidden');
            input.classList.add('invalid');
            input.classList.remove('valid');
            isValid = false;
          }
        });

        // Name fields: no numbers, allow spaces
        nameFields.forEach(name => {
          const input = form.querySelector(`[name="${name}"]`);
          const helpId = `${name}_help`;
          const error = document.getElementById(helpId);
          if (input && input.value.trim() && !/^[a-zA-Z\s]+$/.test(input.value)) {
            error.textContent = `${input.previousElementSibling.textContent.trim()} can only contain letters and spaces.`;
            error.classList.remove('hidden');
            input.classList.add('invalid');
            input.classList.remove('valid');
            isValid = false;
          }
        });

        // Text fields: allow letters, numbers, spaces, and common punctuation
        textFields.forEach(name => {
          const input = form.querySelector(`[name="${name}"]`);
          const helpId = `${name}_help`;
          const error = document.getElementById(helpId);
          if (input && input.value.trim() && !/^[a-zA-Z0-9\s,.()-]+$/.test(input.value)) {
            error.textContent = `${input.previousElementSibling.textContent.trim()} can only contain letters, numbers, spaces, and common punctuation.`;
            error.classList.remove('hidden');
            input.classList.add('invalid');
            input.classList.remove('valid');
            isValid = false;
          }
        });

        // Contact number validation
        const contactNumber = form.querySelector('#contact_number');
        if (contactNumber && contactNumber.value && !/^09\d{9}$/.test(contactNumber.value)) {
          document.getElementById('contact_number_help').classList.remove('hidden');
          contactNumber.classList.add('invalid');
          contactNumber.classList.remove('valid');
          isValid = false;
        }

        // Zip code validation
        const zipCodeInput = form.querySelector('#zip_code');
        if (zipCodeInput && zipCodeInput.value && !/^\d{4,6}$/.test(zipCodeInput.value)) {
          document.getElementById('zip_code_help').classList.remove('hidden');
          zipCodeInput.classList.add('invalid');
          zipCodeInput.classList.remove('valid');
          isValid = false;
        }

        // Email validation
        const email = form.querySelector('#email_address');
        if (email && email.value && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value)) {
          document.getElementById('email_address_help').classList.remove('hidden');
          email.classList.add('invalid');
          email.classList.remove('valid');
          isValid = false;
        }

        // Birthdate validation
        const birthdate = form.querySelector('#birthdate');
        if (birthdate && birthdate.value) {
          const date = new Date(birthdate.value);
          if (isNaN(date.getTime()) || date > new Date()) {
            document.getElementById('birthdate_help').classList.remove('hidden');
            birthdate.classList.add('invalid');
            birthdate.classList.remove('valid');
            isValid = false;
          }
        }

        if (!isValid) {
          form.classList.add('shake');
          setTimeout(() => form.classList.remove('shake'), 500);
          const firstErrorInput = form.querySelector('.error-message:not(.hidden)').previousElementSibling;
          if (firstErrorInput) firstErrorInput.focus();
          return;
        }

        submitBtn.classList.add('submitting');
        btnText.classList.add('hidden');
        spinner.classList.remove('hidden');
        submitBtn.disabled = true;

        form.submit();
      });

      // Polyfill for date input
      if (!document.createElement('input').type.match(/date/)) {
        const birthdateInput = document.getElementById('birthdate');
        if (birthdateInput) {
          birthdateInput.type = 'text';
          birthdateInput.pattern = '\\d{4}-\\d{2}-\\d{2}';
          birthdateInput.placeholder = 'YYYY-MM-DD';
        }
      }
    </script>
    <!-- flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script>
      // Initialize flatpickr on the birthdate field with YYYY-MM-DD format and limited range
      (function() {
        const birth = document.getElementById('birthdate');
        if (birth) {
          flatpickr(birth, {
            dateFormat: 'Y-m-d',
            maxDate: new Date(),
            minDate: '1900-01-01',
            allowInput: true,
            altInput: false,
            onChange: function(selectedDates, dateStr) {
              // keep the value in YYYY-MM-DD format for server-side
              birth.value = dateStr;
            }
          });
        }
      })();
    </script>
  <?php include __DIR__ . '/includes/titlecase_inputs.php'; ?>
</body>
</html>