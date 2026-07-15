<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require 'config.php';
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT first_name, middle_name, last_name, profile_pic FROM scholars WHERE user_id = ?");
$stmt->execute([$user_id]);
$scholar = $stmt->fetch(PDO::FETCH_ASSOC);
// Restrict to scholars only
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Fetch scholar name and profile picture for display (already selected above)

$change_success = '';
$change_error = '';

// Password change logic
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (strlen($new_password) < 6) {
        $change_error = "Password must be at least 6 characters.";
    } elseif ($new_password !== $confirm_password) {
        $change_error = "Passwords do not match.";
    } else {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$hashed, $user_id]);
        $change_success = "Password changed successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Settings | Scholarship Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: url('pictures/iccbackground.png') no-repeat center center fixed;
            background-size: cover;
            background-blend-mode: lighten;
            background-color: rgba(255, 255, 255, 0.85);
            overflow-x: hidden;
        }
        .dropdown-menu {
            display: none;
        }
        .dropdown-menu.show {
            display: block;
        }
    </style>
</head>
<body class="font-sans min-h-screen flex flex-col">
    <div class="absolute top-4 left-4 z-10">
        <img src="pictures/ICC_New-Logo_2022.jpg" alt="ICC Logo" class="w-16 h-16 object-contain rounded-full bg-white shadow-md">
    </div>
    <header class="bg-gradient-to-r from-blue-600 to-blue-400 text-white p-6 md:p-8 text-center shadow-lg">
        <h1 class="text-2xl md:text-3xl font-extrabold flex items-center justify-center">
            <i class="fa fa-cog mr-2"></i> Settings
        </h1>
        <p class="text-sm md:text-base text-white opacity-90 mt-1">
            <?= htmlspecialchars(trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''))) ?>
        </p>
    </header>

    <!-- Profile Dropdown -->
    <div class="absolute top-4 right-4 md:top-6 md:right-6 z-[1050]">
        <button class="flex items-center text-white" onclick="toggleDropdown()">
            <?php if (!empty($scholar['profile_pic']) && file_exists($scholar['profile_pic'])): ?>
                <img src="<?= htmlspecialchars($scholar['profile_pic']) ?>" class="w-10 h-10 object-cover rounded-full border-2 border-white">
            <?php else: ?>
                <i class="fa fa-user-circle text-3xl"></i>
            <?php endif; ?>
           
        </button>
        <ul class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border-none z-50">
            <li><a class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" href="dashboard.php"><i class="fa fa-tachometer-alt mr-2"></i> Dashboard</a></li>
            <li><a class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" href="profile.php"><i class="fa fa-user mr-2"></i> Profile</a></li>
            <li><a class="block px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 flex items-center" href="settings.php"><i class="fa fa-cog mr-2"></i> Settings</a></li>
            <li><hr class="border-gray-200 my-1"></li>
            <li><a class="block px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-500 hover:text-white flex items-center" href="logout.php"><i class="fa fa-sign-out-alt mr-2"></i> Logout</a></li>
        </ul>
    </div>

    <main class="container mx-auto my-6 px-4">
        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-xl p-6 md:p-8">
            <div class="mb-4 text-center">
                <i class="fa fa-shield-alt text-4xl text-blue-600"></i>
                <h2 class="text-xl font-bold text-blue-600 mb-1">Change Password</h2>
                <div class="text-gray-500 text-sm">Update your account password</div>
            </div>
            <?php if ($change_error): ?>
                <div class="mt-4 text-red-600 bg-red-50 border border-red-200 rounded-lg p-2 text-sm"><?= htmlspecialchars($change_error) ?></div>
            <?php elseif ($change_success): ?>
                <div class="mt-4 text-green-600 bg-green-50 border border-green-200 rounded-lg p-2 text-sm"><?= htmlspecialchars($change_success) ?></div>
            <?php endif; ?>
            <form method="POST" autocomplete="off" class="space-y-4">
                <div class="relative">
                    <label for="password" class="block text-sm font-medium text-gray-700 flex items-center"><i class="fa fa-lock mr-2"></i> New Password</label>
                    <input type="password" id="password" name="password" class="w-full p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="6">
                    <button type="button" class="absolute right-4 top-1/2 mt-2 text-gray-500 hover:text-gray-700" onclick="togglePassword('password', this)" tabindex="-1">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                <div class="relative">
                    <label for="confirm_password" class="block text-sm font-medium text-gray-700 flex items-center"><i class="fa fa-lock mr-2"></i> Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="w-full p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500" required minlength="6">
                    <button type="button" class="absolute right-4 top-1/2 mt-2 text-gray-500 hover:text-gray-700" onclick="togglePassword('confirm_password', this)" tabindex="-1">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition flex items-center justify-center gap-2">
                    <i class="fa fa-key mr-2"></i> Change Password
                </button>
            </form>
        </div>
    </main>

    <footer class="text-center text-gray-500 mt-8">&copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.</footer>

    <script>
        function togglePassword(fieldId, btn) {
            const input = document.getElementById(fieldId);
            if (input.type === 'password') {
                input.type = 'text';
                btn.querySelector('i').classList.remove('fa-eye');
                btn.querySelector('i').classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                btn.querySelector('i').classList.remove('fa-eye-slash');
                btn.querySelector('i').classList.add('fa-eye');
            }
        }

        function toggleDropdown() {
            const dropdown = document.querySelector('.dropdown-menu');
            dropdown.classList.toggle('show');
        }

        document.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.dropdown-menu');
            const toggleBtn = document.querySelector('button[onclick="toggleDropdown()"]');
            if (!dropdown.contains(e.target) && !toggleBtn.contains(e.target)) {
                dropdown.classList.remove('show');
            }
        });
    </script>
</body>
</html>