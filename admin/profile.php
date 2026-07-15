<?php
session_start();
require '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit;
}

// Fetch user info
$stmt = $pdo->prepare("SELECT username, profile_pic FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// If username not in session, set it
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = $user['username'];
}

$msg = '';

// Handle profile picture upload (standard or AJAX)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $upload_dir = '../uploads/profile_pics/';

    if (!file_exists($upload_dir)) mkdir($upload_dir, 0755, true);

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $mime = mime_content_type($file['tmp_name']);

    if (in_array($ext, $allowed) && strpos($mime,'image') === 0 && $file['size'] < 2 * 1024 * 1024) {
        $new_name = uniqid('profile_', true) . '.' . $ext;
        $path = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            if ($user['profile_pic'] && file_exists('../' . $user['profile_pic'])) {
                unlink('../' . $user['profile_pic']);
            }

            $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
            $stmt->execute(["uploads/profile_pics/" . $new_name, $_SESSION['user_id']]);

            $_SESSION['profile_pic'] = "uploads/profile_pics/" . $new_name;
            $msg = "Profile picture updated successfully.";
            $user['profile_pic'] = "uploads/profile_pics/" . $new_name;
        } else {
            $msg = "Failed to upload image.";
        }
    } else {
        $msg = "Invalid file type or size (max 2MB).";
    }

    if (!empty($_POST['ajax'])) {
        echo json_encode([
            'success' => isset($user['profile_pic']),
            'message' => $msg,
            'newPath' => $user['profile_pic'] ?? 'pictures/default_avatar.png'
        ]);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body class="bg-gray-50 text-gray-800">
<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="container mx-auto p-4 pt-24">
    <div class="flex justify-center">
        <div class="w-full max-w-md bg-white rounded-lg shadow p-6 text-center">
            
            <?php if ($msg && empty($_POST['ajax'])): ?>
                <div class="bg-blue-100 text-blue-800 px-4 py-2 rounded mb-4 text-sm flex justify-between items-center">
                    <?= htmlspecialchars($msg) ?>
                    <button class="ml-2" onclick="this.parentElement.remove()"><i class="bi bi-x-lg"></i></button>
                </div>
            <?php endif; ?>

            <img src="../<?= htmlspecialchars($user['profile_pic'] ?? 'pictures/default_avatar.png') ?>" 
                 class="w-24 h-24 object-cover rounded-full border-2 border-blue-700 mx-auto mb-4" 
                 alt="Profile Picture" id="profileImg" 
                 onerror="this.src='../pictures/default_avatar.png'">

            <div class="mb-2">
                <span class="text-gray-500 font-medium">Username:</span>
                <span class="text-gray-800 font-semibold ml-1"><?= htmlspecialchars($user['username']) ?></span>
            </div>

            <form id="profileForm" method="post" enctype="multipart/form-data" class="mt-4 flex flex-col gap-3">
                <label for="profile_pic" class="text-sm text-gray-500">Change Profile Picture</label>
                <input type="file" name="profile_pic" id="profile_pic" accept="image/*" class="border rounded px-2 py-1 text-sm">

                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded px-4 py-2 flex items-center justify-center gap-1">
                    <i class="bi bi-upload"></i> Upload
                </button>
            </form>

        </div>
    </div>
</main>

<script>
const profileForm = document.getElementById('profileForm');
const profilePicInput = document.getElementById('profile_pic');
const profileImg = document.getElementById('profileImg');

profileForm.addEventListener('submit', function(e){
    e.preventDefault();

    const file = profilePicInput.files[0];
    if (!file) return alert("Please select a file");

    const formData = new FormData();
    formData.append('profile_pic', file);
    formData.append('ajax', '1');

    fetch('profile.php', { method: 'POST', body: formData })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                profileImg.src = "../" + data.newPath + "?" + new Date().getTime();
                alert(data.message);
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(err => console.error(err));
});
</script>
</body>
</html>
