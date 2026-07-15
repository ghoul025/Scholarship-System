<?php
// Tailwind-based top navbar for main admin area
// Usage: include __DIR__ . '/main_navbar.php';
?>
<style>
/* --- Navbar Styling --- */
.top-navbar {
  height: 56px;
  background-color: #ffffff;
  border-bottom: 1px solid #e5e7eb; 
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
  transition: all 0.3s ease;
  z-index: 50;
  position: fixed;
  width: 100%;
}

/* --- Navbar Logo --- */
.navbar-logo {
  height: 38px;
  width: auto;
  border-radius: 10px;
  border: 1.5px solid #e5e7eb;
  background: #fff;
  padding: 3px;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.navbar-logo:hover {
  transform: scale(1.05);
  box-shadow: 0 3px 8px rgba(0,0,0,0.08);
}

/* --- Nav Links --- */
.nav-link {
  color: #1e3a8a;
  font-weight: 500;
  padding: 0.5rem 1rem;
  border-radius: 6px;
  position: relative;
  transition: all 0.25s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}
.nav-link:hover {
  background-color: #f3f6ff;
  color: #1d4ed8;
}
.nav-link.active {
  color: #1d4ed8;
  font-weight: 600;
  background-color: #e8efff;
}
.nav-link.active::after {
  content: '';
  position: absolute;
  bottom: -3px;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: #2563eb;
  border-radius: 2px;
}

/* --- Profile Picture --- */
.navbar-profile-pic {
  height: 34px;
  width: 34px;
  object-fit: cover;
  border-radius: 50%;
  border: 1.5px solid #e5e7eb;
  background: #fff;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.navbar-profile-pic:hover {
  transform: scale(1.1);
  box-shadow: 0 4px 10px rgba(0,0,0,0.15);
}

/* --- Dropdown Menu --- */
#profileMenu {
  animation: fadeIn 0.2s ease-out;
}
.dropdown-menu .dropdown-item {
  transition: all 0.25s ease;
  border-radius: 6px;
  margin: 2px 4px;
  padding: 8px 10px;
}
.dropdown-menu .dropdown-item:hover {
  background-color: #f3f6ff;
  color: #1d4ed8;
}
.dropdown-menu .dropdown-item.text-danger:hover {
  background-color: #fee2e2;
  color: #b91c1c;
}

/* --- Animations --- */
@keyframes fadeIn {
  from {opacity: 0; transform: translateY(-4px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>

<header class="top-navbar">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between h-14 items-center">
      
      <!-- Logo -->
      <a href="main_admin_dashboard.php" class="flex items-center gap-2">
        <img src="../pictures/ICC_New-Logo_2022.jpg" alt="logo" class="navbar-logo" />
        <span class="font-semibold text-blue-700 text-lg">Scholarship Credential Management System</span>
      </a>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-2">
        <a href="main_admin_dashboard.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])==='main_admin_dashboard.php') ? 'active' : '' ?>">
          <i class="bi bi-shield-lock-fill"></i> Dashboard
        </a>
        <a href="manage_sub_admins.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])==='manage_sub_admins.php') ? 'active' : '' ?>">
          <i class="bi bi-person-gear"></i> Sub-Admins
        </a>
        <a href="main_view_scholars.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])==='main_view_scholars.php') ? 'active' : '' ?>">
          <i class="bi bi-people"></i> Scholars
        </a>
        <a href="main_view_documents.php" class="nav-link <?= (basename($_SERVER['PHP_SELF'])==='main_view_documents.php') ? 'active' : '' ?>">
          <i class="bi bi-file-earmark-check"></i> Documents
        </a>
        
        <!-- Profile Dropdown -->
        <div class="relative">
          <button id="profileBtn" class="flex items-center gap-2 focus:outline-none">
            <img src="<?php echo isset($_SESSION['profile_pic']) && $_SESSION['profile_pic'] ? '../' . htmlspecialchars($_SESSION['profile_pic']) : '../pictures/iccbackground.png'; ?>" class="navbar-profile-pic" alt="Profile">
            <i class="bi bi-caret-down-fill text-gray-500"></i>
          </button>
          <div id="profileMenu" class="hidden absolute right-0 mt-2 w-44 bg-white border border-gray-200 rounded-lg shadow-lg z-50 dropdown-menu">
            <a href="main_admin_profile.php" class="dropdown-item block text-sm text-gray-700"><i class="bi bi-person"></i> Profile</a>
            <a href="main_admin_settings.php" class="dropdown-item block text-sm text-gray-700"><i class="bi bi-gear"></i> Settings</a>
            <div class="border-t my-1"></div>
            <a href="../logout.php" class="dropdown-item block text-sm text-red-600 text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
          </div>
        </div>
      </nav>

      <!-- Mobile menu button -->
      <div class="md:hidden flex items-center">
        <button id="mobile-menu-button" class="p-2 rounded-md bg-gray-100 hover:bg-gray-200 focus:outline-none">
          <i class="bi bi-list text-xl"></i>
        </button>
      </div>
    </div>
  </div>

  <!-- Mobile menu -->
  <div id="mobile-menu" class="md:hidden hidden bg-white border-t border-gray-200 shadow-lg">
    <nav class="px-2 pt-2 pb-4 space-y-1">
      <a href="main_admin_dashboard.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-50 text-gray-700"><i class="bi bi-shield-lock-fill mr-1"></i> Dashboard</a>
      <a href="manage_sub_admins.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-50 text-gray-700"><i class="bi bi-person-gear mr-1"></i> Sub-Admins</a>
      <a href="main_view_scholars.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-50 text-gray-700"><i class="bi bi-people mr-1"></i> Scholars</a>
      <a href="main_view_documents.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-50 text-gray-700"><i class="bi bi-file-earmark-check mr-1"></i> Documents</a>

      <!-- Mobile profile section -->
      <div class="border-t border-gray-200 my-2"></div>
      <div class="flex items-center gap-2 px-3 py-2">
        <img src="<?php echo isset($_SESSION['profile_pic']) && $_SESSION['profile_pic'] ? '../' . htmlspecialchars($_SESSION['profile_pic']) : '../pictures/iccbackground.png'; ?>" class="navbar-profile-pic" alt="Profile">
        <span class="text-gray-700 font-medium truncate">
          <?php
            if (isset($_SESSION['first_name']) || isset($_SESSION['last_name'])) {
              echo htmlspecialchars(trim(($_SESSION['first_name'] ?? '') . ' ' . ($_SESSION['middle_name'] ?? '') . ' ' . ($_SESSION['last_name'] ?? '')));
            } elseif (!empty($_SESSION['full_name'])) {
              echo htmlspecialchars($_SESSION['full_name']);
            } else {
              echo 'Admin';
            }
          ?>
        </span>
      </div>
      <a href="main_admin_profile.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-50 text-gray-700"><i class="bi bi-person mr-1"></i> Profile</a>
      <a href="main_admin_settings.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-blue-50 text-gray-700"><i class="bi bi-gear mr-1"></i> Settings</a>
      <a href="../logout.php" class="block px-3 py-2 rounded-md text-sm font-medium hover:bg-red-50 text-red-600 flex items-center gap-1"><i class="bi bi-box-arrow-right"></i> Logout</a>
    </nav>
  </div>
</header>

<script>
document.addEventListener('DOMContentLoaded', function() {
  const menuButton = document.getElementById('mobile-menu-button');
  const mobileMenu = document.getElementById('mobile-menu');
  const profileBtn = document.getElementById('profileBtn');
  const profileMenu = document.getElementById('profileMenu');

  // Mobile toggle
  menuButton.addEventListener('click', () => {
    mobileMenu.classList.toggle('hidden');
  });

  // Profile dropdown toggle (desktop only)
  profileBtn.addEventListener('click', () => {
    profileMenu.classList.toggle('hidden');
  });

  // Close dropdown if clicked outside
  window.addEventListener('click', (e) => {
    if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
      profileMenu.classList.add('hidden');
    }
  });
});
</script>

<div class="pt-16"><!-- padding for fixed navbar --></div>
