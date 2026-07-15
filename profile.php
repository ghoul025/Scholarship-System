<?php
session_start();
require 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'scholar') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ✅ Fetch scholar info + latest enrollment + school year + batch
$stmt = $conn->prepare("
    SELECT 
        s.first_name, s.middle_name, s.last_name, 
        s.course, s.year_level, s.scholarship_type, 
        s.profile_pic, s.phone, s.sex, s.units, s.tuition_fee,
        s.batch,
    se.enrolled_1st, se.enrolled_2nd, sy.label AS school_year
    FROM scholars s
    LEFT JOIN scholar_enrollments se ON s.id = se.scholar_id
    LEFT JOIN school_years sy ON se.school_year_id = sy.id
    WHERE s.user_id = ?
    ORDER BY sy.start_date DESC
    LIMIT 1
");
$stmt->execute([$user_id]);
$scholar = $stmt->fetch(PDO::FETCH_ASSOC);

// Map semester flags to a simple semester label
if ($scholar) {
    $e1 = !empty($scholar['enrolled_1st']);
    $e2 = !empty($scholar['enrolled_2nd']);
    if ($e1 && $e2) $scholar['semester'] = '1st & 2nd';
    elseif ($e1) $scholar['semester'] = '1st';
    elseif ($e2) $scholar['semester'] = '2nd';
    else $scholar['semester'] = '';
}

$msg = '';

// Handle profile picture upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
    $file = $_FILES['profile_pic'];
    $upload_dir = 'Uploads/profile_pics/';

    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if (in_array($ext, $allowed) && $file['size'] < 2 * 1024 * 1024) {
        $new_name = uniqid('profile_', true) . '.' . $ext;
        $path = $upload_dir . $new_name;

        if (move_uploaded_file($file['tmp_name'], $path)) {
            // Delete old pic if exists
            if (!empty($scholar['profile_pic']) && file_exists($scholar['profile_pic'])) {
                unlink($scholar['profile_pic']);
            }

            // Update DB
            $stmt = $conn->prepare("UPDATE scholars SET profile_pic = ? WHERE user_id = ?");
            $stmt->execute([$path, $user_id]);
            $msg = "Profile picture updated successfully.";
            $scholar['profile_pic'] = $path;
        } else {
            $msg = "<span class='text-red-600'>Failed to upload image.</span>";
        }
    } else {
        $msg = "<span class='text-red-600'>Invalid file type or size (max 2MB).</span>";
    }
}

$profile_pic_folder = 'Uploads/profile_pics/';
$profile_pic = !empty($scholar['profile_pic']) ? $scholar['profile_pic'] : null;
$profile_pic_path = ($profile_pic && file_exists($profile_pic)) ? htmlspecialchars($profile_pic) : null;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
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
            <i class="fa fa-user mr-2"></i> Profile
        </h1>
        <p class="text-sm md:text-base text-white opacity-90 mt-1">
            <?= htmlspecialchars(trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['middle_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''))) ?>
        </p>
    </header>

    <!-- Profile Dropdown -->
    <div class="absolute top-4 right-4 md:top-6 md:right-6 z-[1050]">
        <button class="flex items-center text-white" onclick="toggleDropdown()">
            <?php if ($profile_pic_path): ?>
                <img src="<?= $profile_pic_path ?>" class="w-10 h-10 object-cover rounded-full">
            <?php else: ?>
                <i class="fa fa-user-circle text-3xl"></i>
            <?php endif; ?>
           
        </button>
        <ul class="dropdown-menu absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border-none z-50">
            <li><a class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" href="dashboard.php"><i class="fa fa-tachometer-alt mr-2"></i> Dashboard</a></li>
            <li><a class="block px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 flex items-center" href="profile.php"><i class="fa fa-user mr-2"></i> Profile</a></li>
            <li><a class="block px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" href="settings.php"><i class="fa fa-cog mr-2"></i> Settings</a></li>
            <li><hr class="border-gray-200 my-1"></li>
            <li><a class="block px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-500 hover:text-white flex items-center" href="logout.php"><i class="fa fa-sign-out-alt mr-2"></i> Logout</a></li>
        </ul>
    </div>

    <main class="container mx-auto my-6 px-4">
        <div class="flex justify-center">
            <div class="w-full max-w-2xl bg-white rounded-2xl shadow-xl p-6 md:p-8 text-center">

                <!-- FIX APPLIED HERE: Added flex justify-center -->
                <div class="mb-4 flex justify-center">
                    <?php if ($profile_pic_path): ?>
                        <img src="<?= $profile_pic_path ?>" id="profile-picture" alt="Profile Picture" class="w-28 h-28 object-cover rounded-full mb-4 shadow-md border-2 border-blue-600">
                    <?php else: ?>
                        <i class="fa fa-user-circle text-8xl text-blue-200 mb-4" id="profile-picture-icon"></i>
                    <?php endif; ?>
                </div>

                <h2 class="text-xl md:text-2xl font-bold text-blue-600 mb-1"><?= htmlspecialchars(trim(($scholar['first_name'] ?? '') . ' ' . ($scholar['last_name'] ?? ''))) ?></h2>
                <div class="mb-4 text-gray-500 text-sm">Scholar Profile</div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 mb-4 text-left">
                    <div>
                        <span class="font-semibold text-gray-600">Phone:</span><br>
                        <span class="text-gray-800"><?= htmlspecialchars($scholar['phone'] ?? '-') ?></span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-600">Sex:</span><br>
                        <span class="text-gray-800"><?= htmlspecialchars($scholar['sex'] ?? '-') ?></span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-600">Units:</span><br>
                        <span class="text-gray-800"><?= htmlspecialchars($scholar['units'] ?? '-') ?></span>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-600">Tuition Fee:</span><br>
                        <span class="text-gray-800">₱<?= htmlspecialchars(number_format($scholar['tuition_fee'] ?? 0, 2)) ?></span>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="font-semibold text-gray-600">Course:</span> <span class="text-gray-800"><?= htmlspecialchars($scholar['course']) ?></span><br>
                        <span class="font-semibold text-gray-600">Year Level:</span> <span class="text-gray-800"><?= htmlspecialchars($scholar['year_level']) ?></span><br>
                        <span class="font-semibold text-gray-600">Scholarship Type:</span> <span class="text-gray-800"><?= htmlspecialchars($scholar['scholarship_type']) ?></span><br>
                        <span class="font-semibold text-gray-600">Batch:</span> <span class="text-gray-800"><?= htmlspecialchars($scholar['batch'] ?? 'N/A') ?></span><br>
                        <span class="font-semibold text-gray-600">School Year:</span> <span class="text-gray-800"><?= htmlspecialchars($scholar['school_year'] ?? 'N/A') ?></span><br>
                        <span class="font-semibold text-gray-600">Semester:</span> <span class="text-gray-800"><?= htmlspecialchars($scholar['semester'] ?? 'N/A') ?></span>
                    </div>
                </div>
                <?php if ($msg): ?>
                    <div class="mt-4 text-blue-600 bg-blue-50 border border-blue-200 rounded-lg p-2 text-sm"><?= $msg ?></div>
                <?php endif; ?>
                <form method="post" enctype="multipart/form-data" class="mt-4 space-y-4">
                    <div>
                        <input type="file" class="w-full p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500" id="profile_pic" name="profile_pic" accept="image/*" required>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <i class="fa fa-pencil-square-o mr-2"></i> Change Profile Picture
                    </button>
                    <div class="text-gray-500 text-sm text-center">Accepted formats: .jpg, .jpeg, .png, .gif (Max 2MB)</div>
                </form>
            </div>
        </div>
    </main>

    <footer class="text-center text-gray-500 mt-8">&copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.</footer>

    <script>
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

        // Preview selected image
        $('#profile_pic').on('change', function () {
            const [file] = this.files;
            if (file) {
                $('#profile-picture').attr('src', URL.createObjectURL(file));
            }
        });
    </script>
</body>
</html>
