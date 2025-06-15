<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<header class="bg-white shadow-sm">
    <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8" aria-label="Top">
        <div class="flex h-16 items-center justify-between">
            <div class="flex items-center">
                <a href="dashboard.php" class="flex items-center space-x-2">
                    <svg class="h-8 w-8 text-blue-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                    </svg>
                    <span class="text-xl font-bold text-gray-900">One Heart</span>
                </a>
            </div>

            <div class="hidden md:flex md:items-center md:space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard.php" class="<?php echo ($current_page == 'dashboard.php') ? 'bg-gray-100' : ''; ?> text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md">Dashboard</a>
                    <a href="asnaf.php" class="<?php echo ($current_page == 'asnaf.php') ? 'bg-gray-100' : ''; ?> text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md">Asnaf</a>
                    <a href="members.php" class="<?php echo ($current_page == 'members.php') ? 'bg-gray-100' : ''; ?> text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md">Members</a>

                    <?php if ($_SESSION['user_role'] == 'admin'): ?>
                        <a href="1.php" class="<?php echo ($current_page == '1.php') ? 'bg-gray-100' : ''; ?> text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md">Campaigns</a>
                    <?php endif; ?>

                    <a href="profile.php" class="<?php echo ($current_page == 'profile.php') ? 'bg-gray-100' : ''; ?> text-gray-700 hover:bg-gray-100 px-3 py-2 rounded-md">Profile</a>
                    
                    <a href="logout.php" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="rounded-md border border-gray-300 px-3 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">Login</a>
                    <a href="register.php" class="rounded-md bg-blue-600 px-3 py-2 text-sm font-semibold text-white hover:bg-blue-500">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>