<?php
// Make sure session is started
if (session_status() === PHP_SESSION_NONE) session_start();
?>

<style>
/* Sidebar Styles */
.sidebar {
    height: 100vh;
    width: 220px;
    position: fixed;
    top: 0;
    left: 0;
    background: #0d6efd;
    padding-top: 70px;
    transition: 0.3s;
    z-index: 1000;
    overflow-y: auto;
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
}
.sidebar.collapsed {
    width: 70px;
}
.sidebar ul {
    list-style-type: none;
    padding: 0;
}
.sidebar ul li {
    width: 100%;
}
.sidebar ul li a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    text-decoration: none;
    font-size: 1rem;
    color: #fff;
    border-radius: 12px;
    margin: 5px 10px;
    transition: all 0.3s;
    position: relative;
}
.sidebar ul li a:hover {
    background: rgba(255,255,255,0.15);
    transform: translateX(5px);
}
.sidebar ul li a i {
    font-size: 1.2rem;
    margin-right: 12px;
}
.sidebar ul li a span {
    white-space: nowrap;
}
.sidebar ul li a.active {
    background: rgba(255,255,255,0.25);
}
/* Sidebar Toggle Button */
.sidebar-toggle {
    position: fixed;
    top: 15px;
    left: 230px;
    font-size: 1.5rem;
    cursor: pointer;
    color: #0d6efd;
    transition: left 0.3s;
    z-index: 1100;
}
.sidebar.collapsed ~ .sidebar-toggle {
    left: 80px;
}
</style>

<!-- Sidebar HTML -->
<nav class="sidebar" id="sidebar">
    <ul>
        <li><a href="main_admin_dashboard.php" class="active"><i class="bi bi-shield-lock-fill"></i> <span>Main Admin Dashboard</span></a></li>
        <li><a href="manage_sub_admins.php"><i class="bi bi-person-gear"></i> <span>Manage Sub-Admins</span></a></li>
        <li><a href="main_view_scholars.php"><i class="bi bi-people"></i> <span>View Scholars</span></a></li>
        <li><a href="main_view_documents.php"><i class="bi bi-file-earmark-check"></i> <span>View Documents</span></a></li>
        <li><a href="main_admin_settings.php"><i class="bi bi-gear"></i> <span>Admin Settings</span></a></li>
        <li><a href="main_admin_profile.php"><i class="bi bi-person"></i> <span>Profile</span></a></li>
        <li><a href="../logout.php"><i class="bi bi-box-arrow-right"></i> <span>Logout</span></a></li>
    </ul>
</nav>
<span class="sidebar-toggle" id="sidebarToggle"><i class="bi bi-list"></i></span>

<script>
// Sidebar toggle logic
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('sidebarToggle');
toggleBtn.addEventListener('click', ()=>{
    sidebar.classList.toggle('collapsed');
});
</script>
