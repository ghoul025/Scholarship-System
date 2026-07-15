<?php
session_start();
require '../config.php';

// Only main admin can access
$user_sql = "SELECT * FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !$user || $user['main_admin'] != 1) {
    echo "Access denied. Only the main admin can access this page.";
    exit;
}
// Profile page does not include analytics (analytics are shown on dashboard)
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Main Admin Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-gray-50 min-h-screen font-sans">

    <!-- Navbar -->
    <?php include __DIR__ . '/includes/main_navbar.php'; ?>

    <!-- Header -->
<header class="mt-6 max-w-5xl mx-auto text-center">
    <h1 class="text-4xl font-extrabold flex items-center justify-center gap-3 text-gray-800 animate-fadeIn">
        <i class="bi bi-person-fill"></i> Main Admin Profile
    </h1>
    <p class="mt-2 text-gray-600 text-lg animate-fadeIn delay-150">
        Welcome to your main admin account. Manage your profile and settings securely.
    </p>
</header>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto mt-6 px-4 sm:px-6 lg:px-8">
        <!-- Summary cards removed: analytics belong on the dashboard -->
        <div class="bg-white shadow-lg rounded-xl overflow-hidden animate-fadeIn">
            <div class="bg-gradient-to-r from-blue-600 to-blue-400 text-white px-6 py-4 font-semibold text-lg flex items-center gap-2">
                <i class="bi bi-person-fill"></i> Profile Information
            </div>
            <div class="p-6 space-y-4">
                <dl class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <dt class="text-gray-500 font-medium">Username</dt>
                        <dd class="text-gray-800 font-semibold"><?= htmlspecialchars($user['username']) ?></dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">Role</dt>
                        <dd class="text-gray-800 font-semibold">Main Admin</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 font-medium">User ID</dt>
                        <dd class="text-gray-800 font-semibold"><?= htmlspecialchars($user['id']) ?></dd>
                    </div>
                </dl>
                <div class="bg-blue-50 border border-blue-200 text-blue-700 p-4 rounded-lg flex items-center gap-2">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>This is your main admin account. For security, only password can be changed in <a href="main_admin_settings.php" class="underline font-medium">Settings</a>.</span>
                </div>
            </div>
        </div>
    </main>

    <footer class="text-center text-gray-500 text-sm mt-10 mb-6">
        &copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.
    </footer>

    <style>
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
        .animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
    </style>

</body>
</html>
