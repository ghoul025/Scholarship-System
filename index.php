<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$notifications = [];
if (isset($_SESSION['user_id'])) {
    require_once 'config.php';
    $notif_sql = "SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
    $notif_stmt = $conn->prepare($notif_sql);
    $notif_stmt->execute([$_SESSION['user_id']]);
    $notifications = $notif_stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_read_id'])) {
        $mark_id = intval($_POST['mark_read_id']);
        $mark_sql = "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?";
        $mark_stmt = $conn->prepare($mark_sql);
        $mark_stmt->execute([$mark_id, $_SESSION['user_id']]);
        header("Location: index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>ICCBI Scholarship Management System</title>
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
        .floating-modal {
            display: none;
            animation: fadeIn 0.3s cubic-bezier(0.4, 2, 0.6, 1);
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.5s;
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
    <div class="container mx-auto px-4 py-6 md:py-8">
        <header class="bg-gradient-to-r from-blue-600 to-blue-400 text-white p-4 md:p-6 rounded-xl shadow-lg flex flex-col md:flex-row items-center justify-between mb-6">
            <img src="pictures/ICC_New-Logo_2022.jpg" alt="ICC Logo" class="w-12 h-12 object-contain rounded-full bg-white shadow-md mb-4 md:mb-0">
            <div class="text-center md:text-left">
                <h1 class="text-xl md:text-2xl font-bold flex items-center justify-center md:justify-start">
                    <i class="fa fa-graduation-cap mr-2"></i> ICCBI Scholarship Management System
                </h1>
                <p class="text-sm opacity-90">Improving credential submission for ICC students.</p>
            </div>
            <div class="relative">
                <button class="bg-white text-blue-600 rounded-full p-2 hover:bg-gray-100 transition" onclick="toggleDropdown(event)">
                    <i class="fa fa-bars text-xl"></i>
                </button>
                <ul class="dropdown-menu absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-xl border-none z-50">
                    <li>
                        <button class="w-full text-left px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" onclick="toggleChat('aboutBox')">
                            <i class="fa fa-info-circle mr-2"></i> About Us
                        </button>
                    </li>
                    <li>
                        <button class="w-full text-left px-4 py-2 text-sm font-medium text-gray-700 hover:bg-blue-50 hover:text-blue-600 flex items-center" onclick="toggleChat('contactBox')">
                            <i class="fa fa-envelope mr-2"></i> Contact
                        </button>
                    </li>
                </ul>
            </div>
        </header>

        <div class="flex justify-center">
            <div class="w-full max-w-md md:max-w-lg bg-white rounded-2xl shadow-xl p-6 md:p-8">
                <?php if (isset($_SESSION['application_message'])): ?>
                    <div class="mb-4 p-3 rounded <?= $_SESSION['application_status'] === 'success' ? 'bg-green-100 border-l-4 border-green-600 text-green-800' : 'bg-red-100 border-l-4 border-red-600 text-red-800' ?>">
                        <?= htmlspecialchars($_SESSION['application_message']) ?>
                    </div>
                    <?php unset($_SESSION['application_message'], $_SESSION['application_status']); ?>
                <?php endif; ?>
                <h2 class="text-2xl font-bold text-blue-600 mb-4 flex items-center justify-center">
                    <i class="fa fa-sign-in-alt mr-2"></i> Login
                </h2>
                <p class="text-gray-600 mb-6 text-center">Login to your account.</p>
                <form id="loginForm" class="space-y-4">
                    <div>
                        <input type="text" name="username" class="w-full p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Username" required>
                    </div>
                    <div class="relative">
                        <input type="password" name="password" id="passwordInput" class="w-full p-3 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Password" required>
                        <i class="fa fa-eye-slash absolute right-4 top-1/2 transform -translate-y-1/2 cursor-pointer" onclick="togglePasswordVisibility()"></i>
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white p-3 rounded-full hover:bg-blue-700 transition flex items-center justify-center gap-2">
                        <span class="btn-text">Login</span>
                        <div class="spinner-border w-5 h-5 border-2 border-white rounded-full animate-spin hidden"></div>
                    </button>
                </form>
                <div id="loginMessage" class="mt-4 text-red-600 font-semibold text-center"></div>
                <p class="text-gray-600 text-center mt-4">
                    Don’t have an account or applying for a scholarship?
                    <a href="register_combined.php" class="text-blue-600 font-semibold hover:underline">click here to register / apply</a>
                </p>
            </div>
        </div>

        <div id="aboutBox" class="floating-modal fixed top-24 right-4 md:right-10 w-80 max-w-[90vw] bg-white rounded-2xl shadow-2xl border border-gray-200 z-50">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl cursor-move">
                <h4 class="text-lg font-bold text-blue-600 flex items-center"><i class="fa fa-info-circle mr-2"></i> About Us</h4>
                <button type="button" class="text-gray-600 hover:text-gray-800" aria-label="Close" onclick="closeFloating('aboutBox')"><i class="fa fa-times"></i></button>
            </div>
            <div class="p-4">
                <p class="text-gray-700">We aim to support students in handling their credentials for ease of submission for TES/TDP.</p>
            </div>
        </div>
        <div id="contactBox" class="floating-modal fixed top-24 right-4 md:right-10 w-80 max-w-[90vw] bg-white rounded-2xl shadow-2xl border border-gray-200 z-50">
            <div class="flex justify-between items-center p-4 border-b border-gray-200 bg-gray-50 rounded-t-2xl cursor-move">
                <h4 class="text-lg font-bold text-blue-600 flex items-center"><i class="fa fa-envelope mr-2"></i> Contact</h4>
                <button type="button" class="text-gray-600 hover:text-gray-800" aria-label="Close" onclick="closeFloating('contactBox')"><i class="fa fa-times"></i></button>
            </div>
            <div class="p-4">
                <p class="text-gray-700">Email: elalday25@gmail.com</p>
                <p class="text-gray-700">Phone: 0955 662 2460</p>
            </div>
        </div>

        <footer class="text-center text-gray-500 mt-8">&copy; <?= date('Y'); ?> Scholarship Management System. All rights reserved.</footer>
    </div>

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

        function toggleChat(id) {
            const box = document.getElementById(id);
            const isVisible = box.style.display === 'block';
            document.querySelectorAll('.floating-modal').forEach(el => el.style.display = 'none');
            if (!isVisible) {
                box.style.display = 'block';
                box.style.zIndex = 2000;
            }
        }

        function closeFloating(id) { 
            document.getElementById(id).style.display = 'none'; 
        }

        document.addEventListener('mousedown', function (e) {
            const modals = document.querySelectorAll('.floating-modal');
            let inside = false;
            modals.forEach(modal => { if (modal.contains(e.target)) inside = true; });
            if (!inside && !e.target.closest('.dropdown-menu') && !e.target.closest('button[onclick="toggleDropdown()"]')) {
                modals.forEach(modal => modal.style.display = 'none');
            }
        });

        document.querySelectorAll('.floating-modal > div:first-child').forEach(header => {
            let isDragging = false, startX, startY, startLeft, startTop, modal;
            header.addEventListener('mousedown', function(e) {
                isDragging = true;
                modal = header.parentElement;
                startX = e.clientX; startY = e.clientY;
                const rect = modal.getBoundingClientRect();
                startLeft = rect.left; startTop = rect.top;
                document.body.style.userSelect = 'none';
            });
            document.addEventListener('mousemove', function(e) {
                if (isDragging && modal) {
                    let newLeft = startLeft + (e.clientX - startX);
                    let newTop = startTop + (e.clientY - startY);
                    modal.style.left = newLeft + 'px'; 
                    modal.style.top = newTop + 'px';
                    modal.style.right = 'auto';
                }
            });
            document.addEventListener('mouseup', function() { 
                isDragging = false; 
                document.body.style.userSelect = ''; 
            });
        });

        const loginForm = document.getElementById('loginForm');
        const loginBtn = loginForm.querySelector('button[type="submit"]');
        const btnText = loginBtn.querySelector('.btn-text');
        const spinner = loginBtn.querySelector('.spinner-border');
        const loginMsg = document.getElementById('loginMessage');

        loginForm.addEventListener('submit', function(e){
            e.preventDefault();
            loginMsg.textContent = '';
            spinner.classList.remove('hidden');
            btnText.style.opacity = 0;
            loginBtn.disabled = true;

            const formData = new FormData(this);
            fetch('login.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(data => {
                if(data.success){
                    document.body.style.transition = 'opacity 0.5s';
                    document.body.style.opacity = 0;
                    setTimeout(()=>{ window.location.href = data.redirect; }, 500);
                } else {
                    loginMsg.textContent = data.message;
                    loginForm.classList.add('shake');
                    setTimeout(()=>loginForm.classList.remove('shake'), 500);
                    spinner.classList.add('hidden'); 
                    btnText.style.opacity = 1;
                    loginBtn.disabled = false;
                }
            })
            .catch(err => {
                loginMsg.textContent = 'An error occurred. Please try again.';
                spinner.classList.add('hidden'); 
                btnText.style.opacity = 1; 
                loginBtn.disabled = false;
            });
        });

        function togglePasswordVisibility(){
            const passwordInput = document.getElementById('passwordInput');
            const toggleIcon = document.querySelector('.fa-eye-slash, .fa-eye');
            if(passwordInput.type==='password'){ 
                passwordInput.type='text'; 
                toggleIcon.classList.replace('fa-eye-slash','fa-eye'); 
            } else { 
                passwordInput.type='password'; 
                toggleIcon.classList.replace('fa-eye','fa-eye-slash'); 
            }
        }
    </script>
</body>
</html>