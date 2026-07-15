<?php
session_start();
require '../config.php';

// Only main admin can access
$user_sql = "SELECT main_admin FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !$user || $user['main_admin'] != 1) {
    echo "Access denied. Only the main admin can access this page.";
    exit;
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current = $_POST['current_password'];
    $new = $_POST['new_password'];
    $confirm = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($current, $row['password'])) {
        if ($new === $confirm && strlen($new) >= 6) {
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt->execute([$hashed, $_SESSION['user_id']]);
            $msg = "Password updated successfully!";
        } else {
            $msg = "New passwords do not match or are too short.";
        }
    } else {
        $msg = "Current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Main Admin Settings</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body class="bg-gray-50 min-h-screen font-sans">

    <!-- Navbar -->
    <?php include __DIR__ . '/includes/main_navbar.php'; ?>

    <!-- Page Header -->
    <header class="mt-6 max-w-md mx-auto text-center">
        <h1 class="text-4xl font-extrabold text-gray-800 flex items-center justify-center gap-3 animate-fadeIn">
            <i class="bi bi-key-fill"></i> Change Password
        </h1>
        <p class="mt-2 text-gray-600 text-sm animate-fadeIn delay-150">
            Update your account password securely.
        </p>
    </header>

    <!-- Main Content -->
    <main class="mt-6 max-w-md mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-lg rounded-xl overflow-hidden animate-fadeIn mt-4">
            <div class="bg-blue-100 px-6 py-4 font-semibold text-lg flex items-center gap-2 text-blue-800">
                <i class="bi bi-key-fill"></i> Change Password
            </div>
            <div class="p-6 space-y-4">
                <?php if (isset($msg)): ?>
                    <div class="p-3 rounded bg-blue-50 text-blue-800 font-medium"><?= $msg ?></div>
                <?php endif; ?>
                <form method="post" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Current Password</label>
                        <input type="password" name="current_password" required class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">New Password</label>
                        <input type="password" name="new_password" required minlength="6" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400" />
                    </div>
                    <div>
                        <label class="block text-gray-700 font-medium mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" required minlength="6" class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400" />
                    </div>
                    <button type="submit" name="change_password" class="w-full bg-blue-600 text-white font-semibold py-2 rounded-full shadow hover:bg-blue-500 transition-all">
                        Update Password
                    </button>
                </form>
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
