<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Common stats (from both files)
$asnaf_count = $conn->query("SELECT COUNT(*) FROM asnaf")->fetch_row()[0];
$volunteer_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer'")->fetch_row()[0];
$pending_asnaf = $conn->query("SELECT COUNT(*) FROM asnaf WHERE status = 'pending'")->fetch_row()[0];
$verified_asnaf = $conn->query("SELECT COUNT(*) FROM asnaf WHERE status = 'verified'")->fetch_row()[0];

// Role-specific stats (from dashboard.php)
if ($_SESSION['user_role'] == 'admin') {
    $campaign_count = $conn->query("SELECT COUNT(*) FROM campaigns")->fetch_row()[0];
    $pending_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'pending'")->fetch_row()[0];
} else {
    $my_tasks = $conn->query("
    SELECT COUNT(*) FROM tasks 
    WHERE assigned_to = {$_SESSION['user_id']} AND status = 'pending'")->fetch_row()[0];
$completed_tasks = $conn->query("
    SELECT COUNT(*) FROM tasks 
    WHERE assigned_to = {$_SESSION['user_id']} AND status = 'completed'")->fetch_row()[0];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - One Heart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: all 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="bg-gray-50">
    <?php include 'includes/header.php'; ?>

    <main class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-8">
            <h1 class="text-2xl font-bold tracking-tight text-gray-900">Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600">Welcome back, <?= htmlspecialchars($_SESSION['user_name']) ?></p>
        </div>

        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Total Asnaf Card -->
            <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">Total Asnaf</dt>
                            <dd class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900"><?= $asnaf_count ?></p>
                            </dd>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="asnaf.php" class="font-medium text-blue-600 hover:text-blue-500">View all</a>
                    </div>
                </div>
            </div>

            <!-- Volunteers Card -->
            <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
                <div class="px-4 py-5 sm:p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <div class="ml-5 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 truncate">Volunteers</dt>
                            <dd class="flex items-baseline">
                                <p class="text-2xl font-semibold text-gray-900"><?= $volunteer_count ?></p>
                            </dd>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 px-4 py-4 sm:px-6">
                    <div class="text-sm">
                        <a href="members.php" class="font-medium text-green-600 hover:text-green-500">View all</a>
                    </div>
                </div>
            </div>

            <!-- Pending Asnaf Card -->
            <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
    <div class="px-4 py-5 sm:p-6">
        <div class="flex items-center">
            <div class="flex-shrink-0 bg-yellow-500 rounded-md p-3">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div class="ml-5 w-0 flex-1">
                <dt class="text-sm font-medium text-gray-500 truncate">Pending Asnaf</dt>
                <dd class="flex items-baseline">
                    <p class="text-2xl font-semibold text-gray-900"><?= $pending_asnaf ?></p>
                </dd>
            </div>
        </div>
    </div>
    <div class="bg-gray-50 px-4 py-4 sm:px-6">
        <div class="text-sm">
            <a href="asnaf.php?status=pending" class="font-medium text-yellow-600 hover:text-yellow-500">View pending</a>
        </div>
    </div>
</div>

            <!-- Role-specific cards -->
            <?php if ($_SESSION['user_role'] == 'admin'): ?>
                <!-- Admin Stats -->
                <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Campaigns</dt>
                                <dd class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900"><?= $campaign_count ?></p>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="1.php" class="font-medium text-purple-600 hover:text-purple-500">Manage</a>
                        </div>
                    </div>
                </div>

                <!-- Verified Asnaf Card -->
                <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-400 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Verified Asnaf</dt>
                                <dd class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900"><?= $verified_asnaf ?></p>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="asnaf.php?status=verified" class="font-medium text-blue-400 hover:text-blue-500">View verified</a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <!-- Volunteer Stats -->
                <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-400 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">My Tasks</dt>
                                <dd class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900"><?= $my_tasks ?></p>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="volunteer_tasks.php" class="font-medium text-blue-400 hover:text-blue-500">View tasks</a>
                        </div>
                    </div>
                </div>

                <div class="stat-card bg-white overflow-hidden shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-400 rounded-md p-3">
                                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dt class="text-sm font-medium text-gray-500 truncate">Completed</dt>
                                <dd class="flex items-baseline">
                                    <p class="text-2xl font-semibold text-gray-900"><?= $completed_tasks ?></p>
                                </dd>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-4 sm:px-6">
                        <div class="text-sm">
                            <a href="volunteer_tasks.php" class="font-medium text-green-400 hover:text-green-500">View history</a>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>