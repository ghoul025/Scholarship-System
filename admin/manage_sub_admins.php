<?php
session_start();
require '../config.php';

// Only main admin can access
$user_sql = "SELECT main_admin FROM users WHERE id = ?";
$user_stmt = $conn->prepare($user_sql);
$user_stmt->execute([$_SESSION['user_id']]);
$user = $user_stmt->fetch(PDO::FETCH_ASSOC);

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin' || !$user || $user['main_admin'] != 1) {
    echo "Access denied. Only the main admin can manage sub-admins.";
    exit;
}

// Handle add sub-admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_sub_admin'])) {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'admin';
    $main_admin = 0;

    $sql = "INSERT INTO users (username, password, role, main_admin) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$username, $password, $role, $main_admin]);
    $_SESSION['message'] = "Sub-admin added successfully!";
    header("Location: manage_sub_admins.php");
    exit;
}

// Handle delete sub-admin
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    if ($delete_id != $_SESSION['user_id']) {
        $del_sql = "DELETE FROM users WHERE id = ? AND role = 'admin' AND main_admin = 0";
        $del_stmt = $conn->prepare($del_sql);
        $del_stmt->execute([$delete_id]);
        $_SESSION['message'] = "Sub-admin deleted.";
    }
    header("Location: manage_sub_admins.php");
    exit;
}

// Handle reset password for sub-admin
if (isset($_POST['reset_password_id']) && is_numeric($_POST['reset_password_id'])) {
    $reset_id = $_POST['reset_password_id'];
    $stmt = $conn->prepare("SELECT main_admin FROM users WHERE id = ? AND role = 'admin'");
    $stmt->execute([$reset_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && !$row['main_admin']) {
        $default_password = password_hash('123456', PASSWORD_BCRYPT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
        $update_stmt->execute([$default_password, $reset_id]);
        $_SESSION['message'] = "Password reset to <strong>123456</strong>.";
    } else {
        $_SESSION['message'] = "Cannot reset password for main admin.";
    }
    header("Location: manage_sub_admins.php");
    exit;
}

// Fetch all admins
$admins = $conn->query("SELECT id, username, main_admin FROM users WHERE role = 'admin'")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Sub-Admins</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">

</head>
<body class="bg-gray-50 min-h-screen font-sans">

    <!-- Navbar -->
    <?php include __DIR__ . '/includes/main_navbar.php'; ?>

    <main class="pt-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

    <!-- Page Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-extrabold text-blue-700 mb-1">Manage Sub-Admins</h1>
        <p class="text-gray-600 text-sm">Only the <b>Main Admin</b> can add, remove, or reset sub-admins.</p>
    </div>

    <!-- Messages -->
    <?php if (isset($_SESSION['message'])): ?>
        <div class="mb-6 bg-blue-50 border border-blue-200 text-blue-700 font-medium rounded-lg px-4 py-3 text-center animate-fadeIn">
            <?= $_SESSION['message']; unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <!-- Side-by-Side Layout -->
    <div class="lg:flex lg:space-x-6 space-y-6 lg:space-y-0">

        <!-- Add Sub-Admin Form -->
        <div class="flex-1 bg-white shadow-lg rounded-2xl p-6 animate-fadeIn">
            <h2 class="text-2xl font-semibold text-blue-700 mb-6 flex items-center gap-2">
                <i class="bi bi-person-plus"></i> Add Sub-Admin
            </h2>
            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Username</label>
                    <input type="text" name="username" required placeholder="Enter username"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 shadow-sm transition-all" />
                </div>
                <div>
                    <label class="block text-gray-700 font-medium mb-2">Password</label>
                    <input type="password" name="password" required placeholder="Enter password"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-400 shadow-sm transition-all" />
                </div>
                <button type="submit" name="add_sub_admin"
                        class="w-full bg-gradient-to-r from-blue-600 to-blue-400 text-white font-semibold py-2 rounded-full shadow-lg hover:from-blue-500 hover:to-blue-300 transition-all">
                    <i class="bi bi-person-plus"></i> Add Sub-Admin
                </button>
            </form>
        </div>

        <!-- Admin Accounts Table -->
        <div class="flex-1 bg-white shadow-lg rounded-2xl p-6 animate-fadeIn overflow-x-auto">
            <h2 class="text-2xl font-semibold text-blue-700 mb-6 flex items-center gap-2">
                <i class="bi bi-people"></i> Admin Accounts
            </h2>
            <table class="w-full text-left border-collapse rounded-lg">
                <thead class="bg-blue-50 text-blue-700 font-semibold">
                    <tr>
                        <th class="px-4 py-3">Username</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($admins as $admin): ?>
                        <tr class="hover:bg-blue-50 transition-all">
                            <td class="px-4 py-3"><?= htmlspecialchars($admin['username']) ?></td>
                            <td class="px-4 py-3">
                                <?= $admin['main_admin'] 
                                    ? '<span class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-gradient-to-r from-blue-600 to-blue-400 text-white font-semibold text-sm shadow"><i class="bi bi-star-fill text-yellow-300"></i> Main Admin</span>' 
                                    : '<span class="text-gray-700 font-medium">Sub-Admin</span>' ?>
                            </td>
                            <td class="px-4 py-3 space-x-2">
                                <?php if (!$admin['main_admin']): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="reset_password_id" value="<?= $admin['id'] ?>">
                                        <button type="submit"
                                                class="bg-yellow-400 text-white px-3 py-1 rounded-full text-sm font-semibold hover:bg-yellow-500 transition-all"
                                                onclick="return confirm('Reset password for <?= htmlspecialchars($admin['username']) ?> to 123456?');">
                                            <i class="bi bi-arrow-repeat"></i> Reset
                                        </button>
                                    </form>
                                    <a href="?delete=<?= $admin['id'] ?>"
                                       class="bg-red-600 text-white px-3 py-1 rounded-full text-sm font-semibold hover:bg-red-700 transition-all"
                                       onclick="return confirm('Delete this sub-admin?');">
                                        <i class="bi bi-trash"></i> Delete
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-400 text-sm">Protected</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

<!-- Tailwind FadeIn Animation -->
<style>
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px);} to { opacity:1; transform: translateY(0);} }
.animate-fadeIn { animation: fadeIn 0.6s ease forwards; }
</style>
