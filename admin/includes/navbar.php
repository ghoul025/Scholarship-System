<style>
/* --- Navbar Styling --- */
.top-navbar {
  height: 56px;
  background-color: #ffffff;
  border-bottom: 1px solid #e5e7eb; /* lighter gray */
  box-shadow: 0 2px 6px rgba(0,0,0,0.04);
  transition: all 0.3s ease;
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
  padding: 0.6rem 1rem;
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
  bottom: -4px;
  left: 0;
  width: 100%;
  height: 2px;
  background-color: #2563eb;
  border-radius: 2px;
  transition: width 0.3s ease;
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

<nav class="fixed top-0 left-0 w-full bg-white border-b border-gray-200 shadow-sm z-50">
  <div class="max-w-7xl mx-auto px-4 h-14 flex items-center justify-between">
    
    <!-- Logo -->
    <div class="flex items-center gap-2">
      <img src="../pictures/ICC_New-Logo_2022.jpg" 
           alt="ICC Logo" 
           class="navbar-logo">
    </div>

    <!-- Nav Links -->
    <ul class="hidden md:flex items-center gap-2">
      <li>
        <a href="dashboard.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
           <i class="bi bi-house"></i> Dashboard
        </a>
      </li>
      <li>
        <a href="review_documents.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'review_documents.php' ? 'active' : ''; ?>">
           <i class="bi bi-file-earmark-check"></i> Review Documents
        </a>
      </li>
      <li>
        <a href="manage_scholars.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_scholars.php' ? 'active' : ''; ?>">
           <i class="bi bi-people"></i> Manage Scholars
        </a>
      </li>
      <li>
        <a href="manage_applications.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_applications.php' ? 'active' : ''; ?>">
           <i class="bi bi-person-check-fill"></i> Manage Applications
        </a>
      </li>
      <li>
        <a href="manage_requirements.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_requirements.php' ? 'active' : ''; ?>">
           <i class="bi bi-list-check"></i> Requirements
        </a>
      </li>
      <li>
        <a href="manage_school_years.php" 
           class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'manage_school_years.php' ? 'active' : ''; ?>">
           <i class="bi bi-calendar"></i> School Years
        </a>
      </li>
    </ul>

    <!-- Profile Dropdown -->
    <div class="relative">
      <button id="profileBtn" class="flex items-center gap-2 focus:outline-none">
        <img src="<?php echo isset($_SESSION['profile_pic']) && $_SESSION['profile_pic'] ? '../' . htmlspecialchars($_SESSION['profile_pic']) : '../pictures/iccbackground.png'; ?>" 
             alt="Profile" 
             class="navbar-profile-pic">
        <span class="hidden md:inline-block font-medium text-gray-700 max-w-[120px] truncate">
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
        <i class="bi bi-caret-down-fill text-gray-500"></i>
      </button>

      <!-- Dropdown -->
      <div id="profileMenu" class="hidden absolute right-0 mt-2 w-52 bg-white border border-gray-200 rounded-lg shadow-lg z-50 dropdown-menu">
        <a href="profile.php" class="dropdown-item block text-sm text-gray-700"><i class="bi bi-person"></i> Profile</a>
        <a href="settings.php" class="dropdown-item block text-sm text-gray-700"><i class="bi bi-gear"></i> Settings</a>
        <div class="border-t my-1"></div>
        <a href="../logout.php" class="dropdown-item block text-sm text-red-600 text-danger"><i class="bi bi-box-arrow-right"></i> Logout</a>
      </div>
    </div>
  </div>
</nav>

<script>
  // Dropdown toggle
  const profileBtn = document.getElementById("profileBtn");
  const profileMenu = document.getElementById("profileMenu");

  profileBtn.addEventListener("click", () => {
    profileMenu.classList.toggle("hidden");
  });

  // Close dropdown if clicked outside
  window.addEventListener("click", (e) => {
    if (!profileBtn.contains(e.target) && !profileMenu.contains(e.target)) {
      profileMenu.classList.add("hidden");
    }
  });
</script>