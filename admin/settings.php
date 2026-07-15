<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

$msg = '';
// Fetch current password hash
$stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Handle password change with verification and confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'], $_POST['confirm_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (!password_verify($current_password, $user['password'])) {
        $msg = "<span class='text-red-600'>Current password is incorrect.</span>";
    } elseif ($new_password !== $confirm_password) {
        $msg = "<span class='text-red-600'>New passwords do not match.</span>";
    } elseif (strlen($new_password) < 6) {
        $msg = "<span class='text-red-600'>New password must be at least 6 characters.</span>";
    } else {
        $new_hashed = password_hash($new_password, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
        $stmt->execute([$new_hashed, $_SESSION['user_id']]);
        $msg = "<span class='text-green-600'>Password updated!</span>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Settings</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-50 text-gray-800">
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="container mx-auto p-4 pt-24">
    <div class="flex justify-center">
        <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
            <h2 class="text-2xl font-semibold text-blue-700 text-center mb-4">Change Password</h2>

            <?php if ($msg): ?>
                <div class="mb-4 text-center"><?= $msg ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="off" class="flex flex-col gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                    <input type="password" name="current_password" required
                           class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                    <input type="password" name="new_password" required
                           class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                    <input type="password" name="confirm_password" required
                           class="w-full border border-gray-300 rounded px-3 py-2 shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                </div>

                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded px-4 py-2 mt-2 transition-colors">
                    <i class="bi bi-key-fill mr-1"></i> Change Password
                </button>
            </form>
        </div>
    </div>
</main>


</body>
</html>
