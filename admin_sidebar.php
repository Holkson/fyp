<?php
// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}
?>
<!-- Sidebar -->
<div class="sidebar bg-blue-800 text-white w-64 fixed h-full">
    <div class="p-4 border-b border-blue-700">
        <h1 class="text-xl font-bold">One Heart Admin</h1>
        <p class="text-blue-200 text-sm">Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
    </div>
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="admin_dashboard.php" class="flex items-center p-2 rounded hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF']) == 'admin_dashboard.php' ? 'bg-blue-700' : '' ?>">
                    <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
                </a>
            </li>
            <li>
                <a href="admin_users.php" class="flex items-center p-2 rounded hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF']) == 'admin_users.php' ? 'bg-blue-700' : '' ?>">
                    <i class="fas fa-users mr-3"></i> User Management
                </a>
            </li>
            <li>
                <a href="admin_asnaf.php" class="flex items-center p-2 rounded hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF']) == 'admin_asnaf.php' ? 'bg-blue-700' : '' ?>">
                    <i class="fas fa-hands-helping mr-3"></i> Asnaf Management
                </a>
            </li>
            <li>
                <a href="admin_kampung.php" class="flex items-center p-2 rounded hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF']) == 'admin_kampung.php' ? 'bg-blue-700' : '' ?>">
                    <i class="fas fa-calendar-alt mr-3"></i> Group Management
                </a>
            </li>
            <li>
                <a href="admin_tasks.php" class="flex items-center p-2 rounded hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF']) == 'admin_tasks.php' ? 'bg-blue-700' : '' ?>">
                    <i class="fas fa-calendar-alt mr-3"></i> Task Management
                </a>
            </li>
            <li>
                <a href="admin_reports.php" class="flex items-center p-2 rounded hover:bg-blue-700 <?= basename($_SERVER['PHP_SELF']) == 'admin_reports.php' ? 'bg-blue-700' : '' ?>">
                    <i class="fas fa-chart-bar mr-3"></i> Reports
                </a>
            </li>
            <li>
                <a href="logout.php" class="flex items-center p-2 rounded hover:bg-blue-700">
                    <i class="fas fa-sign-out-alt mr-3"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
</div>