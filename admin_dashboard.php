<?php
session_start();
require_once 'config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'admin') {
    header('Location: ../login.php');
    exit();
}

// Fetch dashboard statistics
$volunteers_count = $conn->query("SELECT COUNT(*) FROM users WHERE role = 'volunteer'")->fetch_row()[0];
$asnaf_count = $conn->query("SELECT COUNT(*) FROM asnaf")->fetch_row()[0];
$pending_asnaf = $conn->query("SELECT COUNT(*) FROM asnaf WHERE status = 'pending'")->fetch_row()[0];
$kampung_count = $conn->query("SELECT COUNT(*) FROM kampung_groups")->fetch_row()[0];
$pending_tasks = $conn->query("SELECT COUNT(*) FROM tasks WHERE status = 'pending'")->fetch_row()[0];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - One Heart</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
        }
        .sidebar {
            min-height: calc(100vh - 4rem);
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex">
        <?php include 'admin_sidebar.php'; ?>

        <!-- Main Content -->
        <div class="flex-1 ml-64">
            <header class="bg-white shadow-sm p-4">
                <h2 class="text-xl font-semibold text-gray-800">Admin Dashboard</h2>
            </header>

            <main class="p-6">
                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="stat-card bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Volunteers</p>
                                <h3 class="text-2xl font-bold"><?= $volunteers_count ?></h3>
                            </div>
                        </div>
                        <a href="admin_users.php" class="mt-4 inline-block text-blue-600 hover:text-blue-800 text-sm">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="stat-card bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-hands-helping fa-lg"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Asnaf</p>
                                <h3 class="text-2xl font-bold"><?= $asnaf_count ?></h3>
                            </div>
                        </div>
                        <a href="admin_asnaf.php" class="mt-4 inline-block text-green-600 hover:text-green-800 text-sm">
                            View all <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="stat-card bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-clock fa-lg"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Pending Asnaf</p>
                                <h3 class="text-2xl font-bold"><?= $pending_asnaf ?></h3>
                            </div>
                        </div>
                        <a href="admin_asnaf.php?status=pending" class="mt-4 inline-block text-yellow-600 hover:text-yellow-800 text-sm">
                            Review <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <!-- Kampung Groups Card -->
                    <div class="stat-card bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-map-marker-alt fa-lg"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Kampung Groups</p>
                                <h3 class="text-2xl font-bold"><?= $kampung_count ?></h3>
                            </div>
                        </div>
                        <a href="admin_kampung.php" class="mt-4 inline-block text-purple-600 hover:text-purple-800 text-sm">
                            Manage <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <!-- Pending Tasks Card -->
                    <div class="stat-card bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-tasks fa-lg"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Pending Tasks</p>
                                <h3 class="text-2xl font-bold"><?= $pending_tasks ?></h3>
                            </div>
                        </div>
                        <a href="admin_tasks.php" class="mt-4 inline-block text-yellow-600 hover:text-yellow-800 text-sm">
                            Review <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>

                <!-- Quick Actions - Updated -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <a href="admin_add_asnaf.php" class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-user-plus fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold">Add New Asnaf</h3>
                                <p class="text-sm text-gray-500">Register new asnaf profile</p>
                            </div>
                        </div>
                    </a>

                    <a href="admin_create_task.php" class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-tasks fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold">Create Task</h3>
                                <p class="text-sm text-gray-500">Assign new task to volunteers</p>
                            </div>
                        </div>
                    </a>

                    <a href="admin_assign_volunteers.php" class="bg-white rounded-lg shadow p-6 hover:bg-gray-50 transition">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-user-check fa-lg"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold">Assign Volunteers</h3>
                                <p class="text-sm text-gray-500">Assign volunteers to kampung</p>
                            </div>
                        </div>
                    </a>
                </div>
            </main>
        </div>
    </div>
</body>
</html>